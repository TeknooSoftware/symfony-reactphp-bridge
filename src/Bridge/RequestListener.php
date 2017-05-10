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

use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response as ReactResponse;
use React\Stream\ReadableStreamInterface;

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
     * To get a new instance bridge, by cloning, to handle the new request from ReactPHP.
     *
     * @param ServerRequestInterface  $request
     * @param ReactResponse $response
     *
     * @return RequestBridge
     */
    private function getRequestBridge(ServerRequestInterface $request, ReactResponse $response): RequestBridge
    {
        $bridge = clone $this->bridge;
        $bridge->handle($request, $response);

        return $bridge;
    }

    /**
     * To run directly the bridge with request without body-entity (like GET request): Any request without
     * Content-Length or Transfer-Encoding headers.
     *
     * @param RequestBridge $bridge
     *
     * @return RequestListener
     */
    private function runRequestWithNoBody(RequestBridge $bridge): RequestListener
    {
        $bridge();

        return $this;
    }

    /**
     * To register the bridge to be executed on data event to execute a request a body-entity, (like POST request):
     * Any request with Content-Length or Transfer-Encoding headers.
     *
     * @param ServerRequestInterface  $request
     * @param RequestBridge $bridge
     *
     * @return RequestListener
     */
    private function runRequestWithBody(
        ServerRequestInterface $request,
        RequestBridge $bridge
    ): RequestListener {
        $body = $request->getBody();

        if (!$body instanceof ReadableStreamInterface) {
            throw new \RuntimeException('Error, the request body must implement ReadableStreamInterface');
        }

        $body->on('data', function () use ($bridge) {
            $bridge();
        });

        return $this;
    }

    /**
     * Event executed on request event emited by ReactPHP to execute directly request without body-entity
     * (like GET requests) or register the bridge to be executed on data event.
     *
     * Body are detected if  Ther is a Content-Length or Transfer-Encoding headers. TRACE request can not have a
     * body-entity. (Following rfc2616)
     *
     * @param ServerRequestInterface  $request
     * @param ReactResponse $response
     *
     * @return self
     */
    public function __invoke(ServerRequestInterface $request, ReactResponse $response)
    {
        $bridge = $this->getRequestBridge($request, $response);

        if ('TRACE' !== \strtoupper($request->getMethod())
            && ($request->hasHeader('Content-Length') || $request->hasHeader('Transfer-Encoding'))) {
            $this->runRequestWithBody($request, $bridge);
        } else {
            $this->runRequestWithNoBody($bridge);
        }

        return $this;
    }
}
