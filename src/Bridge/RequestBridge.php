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
use Symfony\Bridge\PsrHttpMessage\Factory\DiactorosFactory;
use Symfony\Bridge\PsrHttpMessage\HttpFoundationFactoryInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
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
 */
class RequestBridge
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var DatesService
     */
    private $datesService;

    /**
     * @var HttpFoundationFactoryInterface
     */
    private $httpFoundationFactory;

    /**
     * @var DiactorosFactory
     */
    private $diactorosFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RequestBridge constructor.
     *
     * @param KernelInterface                $kernel
     * @param DatesService                   $datesService
     * @param HttpFoundationFactoryInterface $foundationFactory
     * @param DiactorosFactory               $diactorosFactory
     */
    public function __construct(
        KernelInterface $kernel,
        DatesService $datesService,
        HttpFoundationFactoryInterface  $foundationFactory,
        DiactorosFactory $diactorosFactory
    ) {
        $this->kernel = $kernel;
        $this->datesService = $datesService;
        $this->httpFoundationFactory = $foundationFactory;
        $this->diactorosFactory = $diactorosFactory;
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
     * @param ServerRequestInterface $request
     * @param \Throwable             $error
     */
    private function logError(ServerRequestInterface $request, \Throwable $error)
    {
        if (!$this->logger instanceof LoggerInterface) {
            return;
        }

        $date = $this->datesService->getNow();

        $server = $request->getServerParams();

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
     * @param callable       $resolve
     *
     * @return RequestBridge
     */
    private function executePreparedRequest(SymfonyRequest $request, callable $resolve): RequestBridge
    {
        $sfResponse = $this->kernel->handle($request);

        $resolve($this->diactorosFactory->createResponse($sfResponse));

        $this->terminate($request, $sfResponse);
        $this->logRequest($request, $sfResponse);

        return $this;
    }

    /**
     * Called by the RequestListener or when ReactPHP emit the data event to convert the ReactPHP Request to a Symfony
     * Request and execute it with Symfony before send result to ReactPHP.
     *
     * @param ServerRequestInterface $request
     * @param callable               $resolve
     *
     * @return RequestBridge
     */
    public function run(ServerRequestInterface $request, callable $resolve): RequestBridge
    {
        try {
            $sfRequest = $this->httpFoundationFactory->createRequest($request);

            return $this->executePreparedRequest($sfRequest, $resolve);
        } catch (HttpException $error) {
            $this->logError($request, $error);
            $resolve(new ReactResponse($error->getStatusCode(), $error->getHeaders(), $error->getMessage()));
        } catch (\Throwable $error) {
            $this->logError($request, $error);
            $resolve(new ReactResponse(500, [], $error->getMessage()));
        }

        return $this;
    }
}
