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

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;
use React\Http\Response;
use React\Stream\ReadableStreamInterface;
use React\Promise\Promise;
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

        $this->getBridge()
            ->expects(self::once())
            ->method('run')
            ->willReturnCallback(function ($request, $resolv) {
                self::assertInstanceOf(ServerRequestInterface::class, $request);
                $resolv($this->createMock(ResponseInterface::class));

                return $this->getBridge();
            });

        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testTraceMethod()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', true],
        ]);
        $request->expects(self::any())->method('getMethod')->willReturn('trace');

        $this->getBridge()
            ->expects(self::once())
            ->method('run')
            ->with($request)
            ->willReturnCallback(function ($request, $resolv) {
                self::assertInstanceOf(ServerRequestInterface::class, $request);
                $resolv($this->createMock(ResponseInterface::class));

                return $this->getBridge();
            });


        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testWithContentLengthBodyMethod()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false],
        ]);
        $request->expects(self::once())->method('withBody')->willReturnSelf();
        $request->expects(self::once())->method('withParsedBody')->willReturnSelf();

        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);

        $body->expects(self::exactly(2))
            ->method('on')
            ->withConsecutive(['data'],['end'])
            ->willReturnCallback(function ($event, $callback) use ($request) {
                if ('data' == $event) {
                    $callback('foo=bar');
                } else {
                    $callback();
                }

                return $request;
            });

        $this->getBridge()
            ->expects(self::once())
            ->method('run')
            ->with($request)
            ->willReturnCallback(function ($request, $resolv) {
                self::assertInstanceOf(ServerRequestInterface::class, $request);
                $resolv($this->createMock(ResponseInterface::class));

                return $this->getBridge();
            });


        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testWithTransfertEncodingBodyMethod()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true],
        ]);
        $request->expects(self::once())->method('withBody')->willReturnSelf();
        $request->expects(self::once())->method('withParsedBody')->willReturnSelf();

        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);
        $body->expects(self::exactly(2))
            ->method('on')
            ->withConsecutive(['data'],['end'])
            ->willReturnCallback(function ($event, $callback) use ($request) {
                if ('data' == $event) {
                    $callback('foo=bar');
                } else {
                    $callback();
                }

                return $request;
            });

        $this->getBridge()
            ->expects(self::once())
            ->method('run')
            ->with($request)
            ->willReturnCallback(function ($request, $resolv) {
                self::assertInstanceOf(ServerRequestInterface::class, $request);
                $resolv($this->createMock(ResponseInterface::class));

                return $this->getBridge();
            });


        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testWithContentLengthBodyMethodBadBodyEncoding()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false],
        ]);
        $request->expects(self::once())->method('withBody')->willReturnSelf();
        $request->expects(self::once())->method('withParsedBody')->willReturnSelf();

        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);

        $body->expects(self::exactly(2))
            ->method('on')
            ->withConsecutive(['data'],['end'])
            ->willReturnCallback(function ($event, $callback) use ($request) {
                if ('data' == $event) {
                    $callback(123);
                } else {
                    $callback();
                }

                return $request;
            });

        $this->getBridge()
            ->expects(self::once())
            ->method('run')
            ->with($request)
            ->willReturnCallback(function ($request, $resolv) {
                self::assertInstanceOf(ServerRequestInterface::class, $request);
                $resolv($this->createMock(ResponseInterface::class));

                return $this->getBridge();
            });


        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testWithTransfertEncodingBodyMethodBadBodyEncoding()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true],
        ]);
        $request->expects(self::once())->method('withBody')->willReturnSelf();
        $request->expects(self::once())->method('withParsedBody')->willReturnSelf();

        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);

        $body->expects(self::exactly(2))
            ->method('on')
            ->withConsecutive(['data'],['end'])
            ->willReturnCallback(function ($event, $callback) use ($request) {
                if ('data' == $event) {
                    $callback(123);
                } else {
                    $callback();
                }

                return $request;
            });

        $this->getBridge()
            ->expects(self::once())
            ->method('run')
            ->with($request)
            ->willReturnCallback(function ($request, $resolv) {
                self::assertInstanceOf(ServerRequestInterface::class, $request);
                $resolv($this->createMock(ResponseInterface::class));

                return $this->getBridge();
            });


        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testWithContentLengthBodyMethodBadRequestBehavior()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false],
        ]);
        $request->expects(self::once())->method('withBody')->willReturn(null);;

        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);

        $body->expects(self::exactly(2))
            ->method('on')
            ->withConsecutive(['data'],['end'])
            ->willReturnCallback(function ($event, $callback) use ($request) {
                if ('data' == $event) {
                    $callback(123);
                } else {
                    $callback();
                }

                return $request;
            });

        $this->getBridge()
            ->expects(self::never())
            ->method('run');


        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testWithTransfertEncodingBodyMethodBadRequestBehavior()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true],
        ]);
        $request->expects(self::once())->method('withBody')->willReturn(null);

        $body = $this->createMock(ReadableStreamInterface::class);
        $request->expects(self::any())->method('getBody')->willReturn($body);
        $body->expects(self::exactly(2))
            ->method('on')
            ->withConsecutive(['data'],['end'])
            ->willReturnCallback(function ($event, $callback) use ($request) {
                if ('data' == $event) {
                    $callback(123);
                } else {
                    $callback();
                }

                return $request;
            });

        $this->getBridge()
            ->expects(self::never())
            ->method('run');

        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function ($result){
            self::assertInstanceOf(ResponseInterface::class, $result);
        }, function () {
            self::fail('an error has been excepted');
        });
    }

    public function testWithContentLengthBodyMethodBodyNotReadableInterface()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', true],
            ['Transfer-Encoding', false],
        ]);

        $this->getBridge()
            ->expects(self::never())
            ->method('run');

        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function (){
            self::fail('an error must been excepted');
        });
    }

    public function testWithTransfertEncodingBodyMethodBodyNotReadableInterface()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())->method('getMethod')->willReturn('post');
        $request->expects(self::any())->method('hasHeader')->willReturnMap([
            ['Content-Length', false],
            ['Transfer-Encoding', true],
        ]);

        $this->getBridge()
            ->expects(self::never())
            ->method('run');

        $listener = $this->buildRequestListener();
        $promise = $listener($request);
        self::assertInstanceOf(Promise::class, $promise);
        $promise->then(function (){
            self::fail('an error must been excepted');
        });
    }
}
