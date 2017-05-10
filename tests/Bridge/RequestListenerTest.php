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

use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\Response;
use React\Stream\ReadableStreamInterface;
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

    public function testWithNoBody()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('GET');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', false],
        ]);

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response)
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testTraceMethod()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', true],
        ]);
        $request->expects(self::any())->method('getMethod')->willReturn('trace');

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response)
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testWithContentLengthBodyMethod()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false],
        ]);
        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);
        $body->expects(self::once())
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
            ->with($request, $response)
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testWithTransfertEncodingBodyMethod()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true],
        ]);
        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);
        $body->expects(self::once())
            ->method('on')
            ->with('data')
            ->willReturnCallback(function ($event, $callback) use ($request) {
                self::assertEquals('data', $event);
                $callback('foo=bar');

                return $request;
            });

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response)
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWithContentLengthBodyMethodBodyNotReadableInterface()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false],
        ]);
        $body = $this->createMock(StreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::never())
            ->method('__invoke')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testWithTransfertEncodingBodyMethodBodyNotReadableInterface()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true],
        ]);
        $body = $this->createMock(StreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::never())
            ->method('__invoke')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }
}
