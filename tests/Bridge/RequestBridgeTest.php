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

use Psr\Log\LoggerInterface;
use React\Http\Request;
use React\Http\Response;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Teknoo\ReactPHPBundle\Bridge\RequestBridge;
use Teknoo\ReactPHPBundle\Bridge\RequestBuilder;
use Teknoo\ReactPHPBundle\Service\DatesService;

/**
 * Class RequestBridgeTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\Bridge\RequestBridge
 */
class RequestBridgeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DatesService
     */
    private $datesService;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @return KernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getKernel(): KernelInterface
    {
        if (!$this->kernel instanceof KernelInterface) {
            $this->kernel = $this->createMock(KernelInterface::class);
        }

        return $this->kernel;
    }

    /**
     * @return LoggerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLogger(): LoggerInterface
    {
        if (!$this->logger instanceof LoggerInterface) {
            $this->logger = $this->createMock(LoggerInterface::class);
        }

        return $this->logger;
    }

    /**
     * @return DatesService|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDatesService(): DatesService
    {
        if (!$this->datesService instanceof DatesService) {
            $this->datesService = $this->createMock(DatesService::class);
        }

        return $this->datesService;
    }

    /**
     * @return RequestBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getRequestBuilder(): RequestBuilder
    {
        if (!$this->requestBuilder instanceof RequestBuilder) {
            $this->requestBuilder = $this->createMock(RequestBuilder::class);
        }

        return $this->requestBuilder;
    }

    /**
     * @return RequestBridge
     */
    public function buildRequestBridge()
    {
        return new RequestBridge($this->getKernel(), $this->getDatesService(), $this->getRequestBuilder());
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNoRequestDefined()
    {
        $bridge = $this->buildRequestBridge();

        $bridge();
    }

    public function testWithNoBodyNoTerminateKernel()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setMethod')->with('GET')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(null)->willReturnSelf();

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge());
    }

    public function testWithBodyNoTerminateKernel()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setMethod')->with('POST')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(\http_build_query(['foo' => 'bar']))->willReturnSelf();

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'POST');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        $content = \http_build_query(['foo' => 'bar']);
        self::assertInstanceOf(RequestBridge::class, $bridge($content));
    }

    public function testWithNoBodyNoTerminateKernelNotFound()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('setMethod')->with('GET')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(null)->willReturnSelf();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)
            ->willThrowException(new NotFoundHttpException('Not found'));

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge());
    }

    public function testWithBodyNoTerminateKernelNotFound()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('setMethod')->with('GET')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(\http_build_query(['foo' => 'bar']))->willReturnSelf();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)
            ->willThrowException(new NotFoundHttpException('Not found'));

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        $content = \http_build_query(['foo' => 'bar']);
        self::assertInstanceOf(RequestBridge::class, $bridge($content));
    }

    public function testWithNoBodyNoTerminateKernelError()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('setMethod')->with('GET')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(null)->willReturnSelf();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)
            ->willThrowException(new \Exception('Error'));

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge());
    }

    public function testWithBodyNoTerminateKernelError()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('setMethod')->with('GET')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(\http_build_query(['foo' => 'bar']))->willReturnSelf();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)
            ->willThrowException(new \Exception('Error'));

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        $content = \http_build_query(['foo' => 'bar']);
        self::assertInstanceOf(RequestBridge::class, $bridge($content));
    }

    public function testWithNoBodyNoTerminateKernelErrorWithLogger()
    {
        $request = $this->createMock(Request::class);
        $request->remoteAddress = '123.123.123.123';

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('setMethod')->with('GET')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(null)->willReturnSelf();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)
            ->willThrowException(new \Exception('Error'));

        $date = new \DateTime('2017-04-27 14:13:12');
        $this->getDatesService()
            ->expects(self::once())
            ->method('getNow')
            ->willReturn($date);

        $this->getLogger()
            ->expects(self::once())
            ->method('error')
            ->with($this->callback(
                function ($message) {
                    return 0 === strpos($message, '123.123.123.123 - [ Error in');
                }
            ));

        $bridge = $this->buildRequestBridge();
        $bridge->setLogger($this->getLogger());

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge());
    }

    public function testWithBodyNoTerminateKernelErrorWithLogger()
    {
        $request = $this->createMock(Request::class);
        $request->remoteAddress = '123.123.123.123';

        $response = $this->createMock(Response::class);

        $requestBuilder = $this->getRequestBuilder();
        $requestBuilder->expects(self::once())->method('setMethod')->with('POST')->willReturnSelf();
        $requestBuilder->expects(self::once())->method('setContent')->with(\http_build_query(['foo' => 'bar']))->willReturnSelf();
        $requestBuilder->expects(self::once())->method('buildRequest')->with($request)
            ->willThrowException(new \Exception('Error'));

        $date = new \DateTime('2017-04-27 14:13:12');
        $this->getDatesService()
            ->expects(self::once())
            ->method('getNow')
            ->willReturn($date);

        $this->getLogger()
            ->expects(self::once())
            ->method('error')
            ->with($this->callback(
                function ($message) {
                    return 0 === strpos($message, '123.123.123.123 - [ Error in');
                }
            ));

        $bridge = $this->buildRequestBridge();
        $bridge->setLogger($this->getLogger());

        $bridge = $bridge->handle($request, $response, 'POST');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        $content = \http_build_query(['foo' => 'bar']);
        self::assertInstanceOf(RequestBridge::class, $bridge($content));
    }

    public function testCloneKernel()
    {
        $requestBridge = clone $this->buildRequestBridge();
        self::assertInstanceOf(RequestBridge::class, $requestBridge);

        $rProperty = new \ReflectionProperty(RequestBridge::class, 'kernel');
        $rProperty->setAccessible(true);
        self::assertNotSame($rProperty->getValue($requestBridge), $this->getKernel());
    }

    public function testExecutePreparedRequestNoTerminateKernel()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);
        $response->expects(self::once())->method('writeHead')->with(200, []);
        $response->expects(self::once())->method('end')->with('fooBar');

        $sfRequest = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('GET');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(\Symfony\Component\HttpFoundation\Response::class);
        $sfResponse->expects(self::once())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::once())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof \Symfony\Component\HttpFoundation\Request;
            }))
            ->willReturn($sfResponse);

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge->executePreparedRequest($sfRequest));
    }

    public function testExecutePreparedRequestTerminateKernel()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);
        $response->expects(self::once())->method('writeHead')->with(200, []);
        $response->expects(self::once())->method('end')->with('fooBar');

        $sfRequest = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('GET');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(\Symfony\Component\HttpFoundation\Response::class);
        $sfResponse->expects(self::any())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::any())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->kernel = $this->createMock(Kernel::class);
        $this->getKernel()
            ->expects(self::once())
            ->method('terminate');

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof \Symfony\Component\HttpFoundation\Request;
            }))
            ->willReturn($sfResponse);

        $bridge = $this->buildRequestBridge();

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge->executePreparedRequest($sfRequest));
    }

    public function testExecutePreparedRequestTerminateKernelWithLogger()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);
        $response->expects(self::once())->method('writeHead')->with(200, []);
        $response->expects(self::once())->method('end')->with('fooBar');

        $sfRequest = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('GET');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(\Symfony\Component\HttpFoundation\Response::class);
        $sfResponse->expects(self::any())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::any())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->kernel = $this->createMock(Kernel::class);
        $this->getKernel()
            ->expects(self::once())
            ->method('terminate');

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof \Symfony\Component\HttpFoundation\Request;
            }))
            ->willReturn($sfResponse);

        $date = new \DateTime('2017-04-27 14:13:12');
        $this->getDatesService()
            ->expects(self::once())
            ->method('getNow')
            ->willReturn($date);

        $this->getLogger()
            ->expects(self::once())
            ->method('info')
            ->with('123.123.123.123 - [27/Apr/2017 14:13:12 +0000] "GET http://hello.world/v1/endpoint" 200 6');

        $bridge = $this->buildRequestBridge();
        $bridge->setLogger($this->getLogger());

        $bridge = $bridge->handle($request, $response, 'GET');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge->executePreparedRequest($sfRequest));
    }

    public function testExecutePreparedRequestNoTerminateKernelWithLogger()
    {
        $request = $this->createMock(Request::class);

        $response = $this->createMock(Response::class);
        $response->expects(self::once())->method('writeHead')->with(200, []);
        $response->expects(self::once())->method('end')->with('fooBar');

        $sfRequest = $this->createMock(\Symfony\Component\HttpFoundation\Request::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(\Symfony\Component\HttpFoundation\Response::class);
        $sfResponse->expects(self::any())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::any())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof \Symfony\Component\HttpFoundation\Request;
            }))
            ->willReturn($sfResponse);

        $date = new \DateTime('2017-04-27 14:13:12');
        $this->getDatesService()
            ->expects(self::once())
            ->method('getNow')
            ->willReturn($date);

        $this->getLogger()
            ->expects(self::once())
            ->method('info')
            ->with('123.123.123.123 - [27/Apr/2017 14:13:12 +0000] "POST http://hello.world/v1/endpoint" 200 6');

        $bridge = $this->buildRequestBridge();
        $bridge->setLogger($this->getLogger());

        $bridge = $bridge->handle($request, $response, 'POST');
        self::assertInstanceOf(RequestBridge::class, $bridge);
        self::assertInstanceOf(RequestBridge::class, $bridge->executePreparedRequest($sfRequest));
    }
}
