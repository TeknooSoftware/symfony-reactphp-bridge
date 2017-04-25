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

    public function testOptionsMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('Options');

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'OPTIONS')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testHeadMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('head');

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'HEAD')
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

    public function testConnectMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('connect');

        $response = $this->createMock(Response::class);

        $this->getBridge()
            ->expects(self::once())
            ->method('handle')
            ->with($request, $response, 'CONNECT')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with(null)
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPostMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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

    public function testPostMethodContentTypeArray()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>['application/x-www-form-urlencoded']]);
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

    public function testPutMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('put');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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
            ->with($request, $response, 'PUT')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPutMethodContentTypeArray()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('put');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>['application/x-www-form-urlencoded']]);
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
            ->with($request, $response, 'PUT')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testDeleteMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('delete');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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
            ->with($request, $response, 'DELETE')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testDeleteMethodContentTypeArray()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('delete');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>['application/x-www-form-urlencoded']]);
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
            ->with($request, $response, 'DELETE')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPatchMethod()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('patch');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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
            ->with($request, $response, 'PATCH')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPatchMethodContentTypeArray()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('patch');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>['application/x-www-form-urlencoded']]);
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
            ->with($request, $response, 'PATCH')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPostMethodWithContinue()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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

    public function testPutMethodWithContinue()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('put');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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
            ->with($request, $response, 'PUT')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testDeleteMethodWithContinue()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('delete');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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
            ->with($request, $response, 'DELETE')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPatchMethodWithContinue()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('patch');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Content-Type'=>'application/x-www-form-urlencoded']);
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
            ->with($request, $response, 'PATCH')
            ->willReturnSelf();

        $this->getBridge()
            ->expects(self::once())
            ->method('__invoke')
            ->with('foo=bar')
            ->willReturnSelf();

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPostMethodBadContentType()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('getHeaders')->willReturn([]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(false);
        $request->expects(self::never())->method('on');

        $response = $this->createMock(Response::class);
        $response->expects(self::never())->method('writeContinue');
        $response->expects(self::once())->method('writeHead')->with(500, []);
        $response->expects(self::once())->method('end')->with('Request not managed');

        $this->getBridge()
            ->expects(self::never())
            ->method('__invoke');

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPutMethodBadContentType()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('put');
        $request->expects(self::any())->method('getHeaders')->willReturn([]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(false);
        $request->expects(self::never())->method('on');

        $response = $this->createMock(Response::class);
        $response->expects(self::never())->method('writeContinue');
        $response->expects(self::once())->method('writeHead')->with(500, []);
        $response->expects(self::once())->method('end')->with('Request not managed');

        $this->getBridge()
            ->expects(self::never())
            ->method('__invoke');

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testDeleteMethodBadContentType()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('delete');
        $request->expects(self::any())->method('getHeaders')->willReturn([]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(false);
        $request->expects(self::never())->method('on');

        $response = $this->createMock(Response::class);
        $response->expects(self::never())->method('writeContinue');
        $response->expects(self::once())->method('writeHead')->with(500, []);
        $response->expects(self::once())->method('end')->with('Request not managed');

        $this->getBridge()
            ->expects(self::never())
            ->method('__invoke');

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }

    public function testPatchMethodBadContentType()
    {
        $request = $this->createMock(Request::class);
        $request->expects(self::any())->method('getMethod')->willReturn('patch');
        $request->expects(self::any())->method('getHeaders')->willReturn([]);
        $request->expects(self::any())->method('expectsContinue')->willReturn(false);
        $request->expects(self::never())->method('on');

        $response = $this->createMock(Response::class);
        $response->expects(self::never())->method('writeContinue');
        $response->expects(self::once())->method('writeHead')->with(500, []);
        $response->expects(self::once())->method('end')->with('Request not managed');

        $this->getBridge()
            ->expects(self::never())
            ->method('__invoke');

        $listener = $this->buildRequestListener();
        self::assertInstanceOf(RequestListener::class, $listener($request, $response));
    }
}
