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

namespace Teknoo\ReactPHPBundle\Bridge;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response as ReactResponse;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\TerminableInterface;
use Teknoo\ReactPHPBundle\Service\DatesService;

/**
 * Class RequestBridge.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @SuppressWarnings(PHPMD)
 */
class RequestBridge
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var ServerRequestInterface
     */
    private $reactRequest;

    /**
     * @var ReactResponse
     */
    private $reactResponse;

    /**
     * @var RequestBuilder
     */
    private $requestBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DatesService
     */
    private $datesService;

    /**
     * RequestBridge constructor.
     *
     * @param KernelInterface $kernel
     * @param DatesService    $datesService
     * @param RequestBuilder  $builder
     */
    public function __construct(
        KernelInterface $kernel,
        DatesService $datesService,
        RequestBuilder  $builder
    ) {
        $this->kernel = $kernel;
        $this->datesService = $datesService;
        $this->requestBuilder = $builder;
    }

    /**
     * To register a logger into the bridge to register request summary and errors.
     *
     * @param LoggerInterface $logger
     *
     * @return self
     */
    public function setLogger(LoggerInterface $logger): RequestBridge
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * To initialize this bridge with ReactPHP Request and Response and the HTTP Method of the current request.
     * Needed to execute this object.
     *
     * @param ServerRequestInterface  $request
     * @param ReactResponse $response
     *
     * @return RequestBridge
     */
    public function handle(ServerRequestInterface $request, ReactResponse $response): RequestBridge
    {
        $this->reactRequest = $request;
        $this->reactResponse = $response;

        return $this;
    }

    /**
     * If the Kernel support Terminate behavior, execute it.
     *
     * @param SymfonyRequest  $request
     * @param SymfonyResponse $response
     *
     * @return self
     */
    private function terminate(SymfonyRequest $request, SymfonyResponse $response): RequestBridge
    {
        if ($this->kernel instanceof TerminableInterface) {
            $this->kernel->terminate($request, $response);
        }

        return $this;
    }

    /**
     * To fail if this bridge is not correctly configured.
     *
     * @return self
     */
    private function checkRequirements()
    {
        if (!$this->reactRequest instanceof ServerRequestInterface
            || !$this->reactResponse instanceof ReactResponse) {
            throw new \RuntimeException('Error, the bridge has not handled the request');
        }

        return $this;
    }

    /**
     * Magic method to clone the Symfony Kernel when this RequestBridge instance is cloned by the listener.
     */
    public function __clone()
    {
        $this->kernel = clone $this->kernel;
    }

    /**
     * To add in the log system the result of the request, following the log format defined for Apache HTTP.
     * If no logger has been defined, this operation is ignored.
     *
     * @param SymfonyRequest  $request
     * @param SymfonyResponse $response
     */
    private function logRequest(SymfonyRequest $request, SymfonyResponse $response)
    {
        if (!$this->logger instanceof LoggerInterface) {
            return;
        }

        $date = $this->datesService->getNow();

        $message = \sprintf(
            '%s - [%s] "%s %s" %s %s',
            $request->getClientIp(),
            $date->format('d/M/Y H:i:s O'),
            $request->getRealMethod(),
            $request->getUri(),
            $response->getStatusCode(),
            \strlen($response->getContent())
        );

        $this->logger->info($message);
    }

    /**
     * To add in the log system an error durring the request.
     * If no logger has been defined, this operation is ignored.
     *
     * @param \Throwable $error
     */
    private function logError(\Throwable $error)
    {
        if (!$this->logger instanceof LoggerInterface) {
            return;
        }

        $date = $this->datesService->getNow();

        $server = $this->reactRequest->getServerParams();

        $message = \sprintf(
            '%s - [%] %s in %s (%s)',
            $server['REMOTE_ADDR'],
            $date->format('d/M/Y H:i:s O'),
            $error->getMessage(),
            $error->getFile(),
            $error->getLine()
        );

        $this->logger->error($message);
    }

    /**
     * Called by the Request builder, when the Symfony Request is ready to execute it with the Symfony Kernel.
     *
     * @param SymfonyRequest $request
     *
     * @return RequestBridge
     */
    public function executePreparedRequest(SymfonyRequest $request): RequestBridge
    {
        $sfResponse = $this->kernel->handle($request);

        $this->reactResponse->writeHead(
            $sfResponse->getStatusCode(),
            $sfResponse->headers->all()
        );

        $this->reactResponse->end($sfResponse->getContent());
        $this->terminate($request, $sfResponse);

        $this->logRequest($request, $sfResponse);

        return $this;
    }

    /**
     * Called by the RequestListener or when ReactPHP emit the data event to convert the ReactPHP Request to a Symfony
     * Request and execute it with Symfony before send result to ReactPHP.
     *
     * @return self
     */
    public function __invoke(): RequestBridge
    {
        $this->checkRequirements();

        try {
            $this->requestBuilder->buildRequest($this->reactRequest, $this);
        } catch (NotFoundHttpException $e) {
            $this->reactResponse->writeHead($e->getStatusCode(), $e->getHeaders());
            $this->reactResponse->end($e->getMessage());
        } catch (\Throwable $e) {
            $this->reactResponse->writeHead(500);
            $this->reactResponse->end($e->getMessage());

            $this->logError($e);
        }

        return $this;
    }
}
