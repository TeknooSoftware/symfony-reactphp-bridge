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
use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Teknoo\ReactPHPBundle\Bridge\RequestBridge;
use Teknoo\ReactPHPBundle\Service\DatesService;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
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
     * @var HttpFoundationFactoryInterface
     */
    private $httpFoundationFactory;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @var DatesService
     */
    private $datesService;

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
     * @return HttpFoundationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getHttpFoundationFactory(): HttpFoundationFactory
    {
        if (!$this->httpFoundationFactory instanceof HttpFoundationFactory) {
            $this->httpFoundationFactory = $this->createMock(HttpFoundationFactory::class);
        }

        return $this->httpFoundationFactory;
    }

    /**
     * @return DiactorosFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getDiactorosFactory(): DiactorosFactory
    {
        if (!$this->diactorosFactory instanceof DiactorosFactory) {
            $this->diactorosFactory = $this->createMock(DiactorosFactory::class);
        }

        return $this->diactorosFactory;
    }
    
    /**
     * @return RequestBridge
     */
    public function buildRequestBridge()
    {
        return new RequestBridge($this->getKernel(), $this->getDatesService(), $this->getHttpFoundationFactory(), $this->getDiactorosFactory());
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
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->createMock(Response::class);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('GET');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(SymfonyResponse::class);
        $sfResponse->expects(self::any())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::any())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getDiactorosFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->with($sfResponse)
            ->willReturn($response);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof SymfonyRequest;
            }))
            ->willReturn($sfResponse);

        $bridge = $this->buildRequestBridge();

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testExecutePreparedRequestTerminateKernel()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->createMock(Response::class);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('GET');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(SymfonyResponse::class);
        $sfResponse->expects(self::any())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::any())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getDiactorosFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->with($sfResponse)
            ->willReturn($response);

        $this->kernel = $this->createMock(Kernel::class);
        $this->getKernel()
            ->expects(self::once())
            ->method('terminate');

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof SymfonyRequest;
            }))
            ->willReturn($sfResponse);

        $bridge = $this->buildRequestBridge();

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testExecutePreparedRequestTerminateKernelWithLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->createMock(Response::class);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('GET');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(SymfonyResponse::class);
        $sfResponse->expects(self::any())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::any())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getDiactorosFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->with($sfResponse)
            ->willReturn($response);

        $this->kernel = $this->createMock(Kernel::class);
        $this->getKernel()
            ->expects(self::once())
            ->method('terminate');

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof SymfonyRequest;
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

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testExecutePreparedRequestNoTerminateKernelWithLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);

        $response = $this->createMock(Response::class);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $sfResponse = $this->createMock(SymfonyResponse::class);
        $sfResponse->expects(self::any())->method('getContent')->willReturn('fooBar');
        $sfResponse->expects(self::any())->method('getStatusCode')->willReturn(200);
        $sfResponse->headers = $this->createMock(ParameterBag::class);
        $sfResponse->headers->expects(self::any())->method('all')->willReturn([]);

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getDiactorosFactory()
            ->expects(self::any())
            ->method('createResponse')
            ->with($sfResponse)
            ->willReturn($response);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->with($this->callback(function ($a) {
                return $a instanceof SymfonyRequest;
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

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithNoBodyNoTerminateKernelErrorWithLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
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

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithBodyNoTerminateKernelErrorWithLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
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

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithNoBodyNoTerminateKernelErrorWithNoLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new \Exception('Error'));

        $bridge = $this->buildRequestBridge();

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithBodyNoTerminateKernelErrorWithNoLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new \Exception('Error'));

        $bridge = $this->buildRequestBridge();

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithNoBodyNoTerminateKernelHttpErrorWithLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new HttpException(400, 'Error'));

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

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithBodyNoTerminateKernelHttpErrorWithLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new HttpException(400, 'Error'));

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

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithNoBodyNoTerminateKernelHttpErrorWithNoLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new HttpException(400, 'Error'));

        $bridge = $this->buildRequestBridge();

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }

    public function testWithBodyNoTerminateKernelHttpErrorWithNoLogger()
    {
        $request = $this->createMock(ServerRequestInterface::class);
        $request->expects(self::any())
            ->method('getServerParams')
            ->willReturn(['REMOTE_ADDR' => '123.123.123.123']);

        $sfRequest = $this->createMock(SymfonyRequest::class);
        $sfRequest->expects(self::any())->method('getClientIp')->willReturn('123.123.123.123');
        $sfRequest->expects(self::any())->method('getRealMethod')->willReturn('POST');
        $sfRequest->expects(self::any())->method('getUri')->willReturn('http://hello.world/v1/endpoint');

        $this->getHttpFoundationFactory()
            ->expects(self::any())
            ->method('createRequest')
            ->with($request)
            ->willReturn($sfRequest);

        $this->getKernel()
            ->expects(self::once())
            ->method('handle')
            ->willThrowException(new HttpException(400, 'Error'));

        $bridge = $this->buildRequestBridge();

        $resolveCalled = false;
        $resolve = function ($response) use (&$resolveCalled) {
            $resolveCalled = true;
            self::assertInstanceOf(ResponseInterface::class, $response);
        };

        self::assertInstanceOf(RequestBridge::class, $bridge->run($request, $resolve));
        self::assertTrue($resolveCalled);
    }
}
