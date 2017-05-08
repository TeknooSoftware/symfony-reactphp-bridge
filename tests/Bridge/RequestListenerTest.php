<?php

/**
 * ReactPHP Symfony Bridge.
 *
 * LICENSE
 *
 * This source file is subject to the MIT license and the version 3 of the GPL3
 * license that are bundled with this package in the folder licences
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to richarddeloge@gmail.com so we can send you a copy immediately.
 *
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/reactphp/symfony Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */

namespace Teknoo\Tests\ReactPHPBundle\Bridge;

use React\Http\Request;
use React\Http\Response;
use Teknoo\ReactPHPBundle\Bridge\RequestBridge;
use Teknoo\ReactPHPBundle\Bridge\RequestListener;

/**
 * Class RequestListenerTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\Bridge\RequestListener
 */
class RequestListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestBridge
     */
    private $bridge;

    /**
     * @return RequestBridge|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getBridge(): RequestBridge
    {
        if (!$this->bridge instanceof RequestBridge) {
            $this->bridge = $this->createMock(RequestBridge::class);
        }

        return $this->bridge;
    }

    /**
     * @return RequestListener
     */
    public function buildRequestListener()
    {
        return new RequestListener($this->getBridge());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testBadHTTPMethodBehavior()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('foo');

        $response = $this->createMock(Response::class);

        $listener = $this->buildRequestListener();
        $listener($request, $response);
    }

    public function testGetMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('get');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', false]
        ]);

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'GET')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testTraceMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', true]
        ]);
        $request->expects(self::any())->method('getMethod')->willReturn('trace');

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'TRACE')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testWithContentLengthBodyMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false]
        ]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(false);
        $request->expects(self::once())
            ->method('on')
            ->with('data')
            ->willReturnCallback(function ($event, $callback) use ($request) {
            self::assertEquals('data', $event);
            $callback('foo=bar');

            return $request;
        });

        $response = $this->createMock(Response::class);
        $response->expects(self::never())->method('writeContinue');

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'POST')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testWithTransfertEncodingBodyMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true]
        ]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(false);
        $request->expects(self::once())
            ->method('on')
            ->with('data')
            ->willReturnCallback(function ($event, $callback) use ($request) {
            self::assertEquals('data', $event);
            $callback('foo=bar');

            return $request;
        });

        $response = $this->createMock(Response::class);
        $response->expects(self::never())->method('writeContinue');

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'POST')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testWithContentLengthBodyMethodWithContinue()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false]
        ]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(true);
        $request->expects(self::once())
            ->method('on')
            ->with('data')
            ->willReturnCallback(function ($event, $callback) use ($request) {
            self::assertEquals('data', $event);
            $callback('foo=bar');

            return $request;
        });

        $response = $this->createMock(Response::class);
        $response->expects(self::once())->method('writeContinue');

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'POST')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testWithTransfertEncodingBodyMethodWithContinue()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true]
        ]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(true);
        $request->expects(self::once())
            ->method('on')
            ->with('data')
            ->willReturnCallback(function ($event, $callback) use ($request) {
            self::assertEquals('data', $event);
            $callback('foo=bar');

            return $request;
        });

        $response = $this->createMock(Response::class);
        $response->expects(self::once())->method('writeContinue');

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'POST')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }
}
