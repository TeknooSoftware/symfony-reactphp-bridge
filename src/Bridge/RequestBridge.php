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
use React\Http\Request as ReactRequest;
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
 */
class RequestBridge
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * Attributes for Symfony requests.
     *
     * @var array
     */
    private $requestAttributes = [];

    /**
     * @var ReactRequest
     */
    private $reactRequest;

    /**
     * @var ReactResponse
     */
    private $reactResponse;

    /**
     * @var string
     */
    private $method;

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
     * @param array           $requestAttributes
     */
    public function __construct(
        KernelInterface $kernel,
        DatesService $datesService,
        array $requestAttributes = []
    ) {
        $this->kernel = $kernel;
        $this->datesService = $datesService;
        $this->requestAttributes = $requestAttributes;
    }

    /**
     * To register a logger into the bridge to register request summary and errors
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
     * @param ReactRequest  $request
     * @param ReactResponse $response
     * @param string        $method
     *
     * @return RequestBridge
     */
    public function handle(ReactRequest $request, ReactResponse $response, string $method): RequestBridge
    {
        $this->reactRequest = $request;
        $this->reactResponse = $response;
        $this->method = $method;

        return $this;
    }

    /**
     * To extract into an array the body formatted content. If the body is empty, the method return an empty array.
     *
     * @param string|null $content
     *
     * @return array
     */
    private function getParsedContent($content)
    {
        if (empty($content)) {
            return [];
        }

        $post = [];
        \parse_str($content, $post);

        return $post;
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
     * Prepare the Symfony request from the ReactPHP request.
     * $_FILES is currently not supported
     * $_COOKIES is currently not supported
     * Simulate $_SERVER.
     *
     * @param array       $query
     * @param array       $bodyParsed
     * @param string|null $rawContent
     *
     * @return SymfonyRequest
     *
     * @SuppressWarnings(PHPMD)
     */
    private function getSymfonyRequest(array $query, array $bodyParsed, string $rawContent = null): SymfonyRequest
    {
        $headers = $this->reactRequest->getHeaders();

        $server = \array_merge(
            $_SERVER,
            [
                'REQUEST_URI' => $this->reactRequest->getPath(),
                'REMOTE_ADDR' => $this->reactRequest->remoteAddress
            ]
        );

        if (isset($headers['Host'][0])) {
            $server['SERVER_NAME'] = \explode(':', $headers['Host'][0]);
        }

        $sfRequest = new SymfonyRequest(
            $query,
            $bodyParsed,
            $this->requestAttributes,
            [], //$_COOKIES is currently not supported
            [], //$_FILES is currently not supported
            $server, // Server is partially filled a few lines below
            $rawContent
        );

        $sfRequest->setMethod($this->method);
        $sfRequest->headers->replace($headers);

        return $sfRequest;
    }

    /**
     * To fail if this bridge is not correctly configured.
     *
     * @return self
     */
    private function checkRequirements()
    {
        if (!$this->reactRequest instanceof ReactRequest
            || !$this->reactResponse instanceof ReactResponse
            || empty($this->method)) {
            throw new \RuntimeException('Error, the bridge has not handled the request');
        }

        return $this;
    }

    /**
     * Magic method to clone the Symfony Kernel when this RequestBridge instance is cloned by the listener
     */
    public function __clone()
    {
        $this->kernel = clone $this->kernel;
    }

    /**
     * To add in the log system the result of the request, following the log format defined for Apache HTTP.
     * If no logger has been defined, this operation is ignored.
     *
     * @param SymfonyRequest $request
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
     * @param \Throwable $e
     */
    private function logError(\Throwable $e)
    {
        if (!$this->logger instanceof LoggerInterface) {
            return;
        }

        $date = $this->datesService->getNow();

        $message = \sprintf(
            '%s - [%] %s in %s (%s)',
            $this->reactRequest->remoteAddress,
            $date->format('d/M/Y H:i:s O'),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );

        $this->logger->error($message);
    }

    /**
     * Called by the RequestListener or when ReactPHP emit the data event to convert the ReactPHP Request to a Symfony
     * Request and execute it with Symfony before send result to ReactPHP.
     *
     * @param string|null $content
     *
     * @return self
     */
    public function __invoke(string $content = null): RequestBridge
    {
        $this->checkRequirements();

        $query = $this->reactRequest->getQueryParams();

        $bodyParsed = $this->getParsedContent($content);

        try {
            $sfRequest = $this->getSymfonyRequest($query, $bodyParsed, $content);

            $sfResponse = $this->kernel->handle($sfRequest);

            $this->reactResponse->writeHead(
                $sfResponse->getStatusCode(),
                $sfResponse->headers->all()
            );

            $this->reactResponse->end($sfResponse->getContent());
            $this->terminate($sfRequest, $sfResponse);

            $this->logRequest($sfRequest, $sfResponse);
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
