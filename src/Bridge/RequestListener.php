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

use React\Http\Request as ReactRequest;
use React\Http\Response as ReactResponse;

/**
 * Class RequestListener.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RequestListener
{
    /**
     * @var RequestBridge
     */
    private $bridge;

    /**
     * RequestListener constructor.
     *
     * @param RequestBridge $bridge
     */
    public function __construct(RequestBridge $bridge)
    {
        $this->bridge = $bridge;
    }

    /**
     * To get a valid HTTP method.
     *
     * @param ReactRequest $request
     *
     * @return string
     *
     * @throws \RuntimeException
     *
     * @SuppressWarnings(PHPMD)
     */
    private function getMethod(ReactRequest $request)
    {
        $method = \strtoupper($request->getMethod());

        switch ($method) {
            case 'OPTIONS':
            case 'HEAD':
            case 'GET':
            case 'TRACE':
            case 'CONNECT':
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                return $method;
                break;
        }

        throw new \RuntimeException(sprintf('Method %s is not recognized', $method));
    }

    /**
     * To get a new instance bridge, by cloning, to handle the new request from ReactPHP.
     *
     * @param ReactRequest  $request
     * @param ReactResponse $response
     * @param string        $method
     *
     * @return RequestBridge
     */
    private function getRequestBridge(ReactRequest $request, ReactResponse $response, string $method): RequestBridge
    {
        $bridge = clone $this->bridge;
        $bridge->handle($request, $response, $method);

        return $bridge;
    }

    /**
     * To run directly the bridge with request without body (like GET request).
     *
     * @param RequestBridge $bridge
     *
     * @return RequestListener
     */
    private function runRequestWithNoBody(RequestBridge $bridge): RequestListener
    {
        $bridge(null);

        return $this;
    }

    /**
     * To register the bridge to be executed on data event.
     *
     * @param ReactRequest  $request
     * @param ReactResponse $response
     * @param RequestBridge $bridge
     *
     * @return RequestListener
     */
    private function runRequestWithBody(
        ReactRequest $request,
        ReactResponse $response,
        RequestBridge $bridge
    ): RequestListener {
        $request->on('data', function ($requestBody) use ($bridge) {
            $bridge($requestBody);
        });

        if ($request->expectsContinue()) {
            $response->writeContinue();
        }

        return $this;
    }

    /**
     * Event executed on request event emited by ReactPHP to execute directly request without body (like GET requests)
     * or register the bridge to be executed on data event.
     *
     * @param ReactRequest  $request
     * @param ReactResponse $response
     *
     * @return self
     */
    public function __invoke(ReactRequest $request, ReactResponse $response)
    {
        $method = $this->getMethod($request);
        $headers = $request->getHeaders();

        $bridge = $this->getRequestBridge($request, $response, $method);

        if (\in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'])) {
            $contentType = '';
            if (isset($headers['Content-Type'])) {
                $contentType = \implode(' ', (array) $headers['Content-Type']);
            }

            if (false !== \strpos($contentType, 'application/x-www-form-urlencoded')) {
                $this->runRequestWithBody($request, $response, $bridge);
            } else {
                $response->writeHead(500);
                $response->end('Request not managed');
            }
        } else {
            $this->runRequestWithNoBody($bridge);
        }

        return $this;
    }
}
