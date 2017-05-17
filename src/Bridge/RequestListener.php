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
use React\Promise\Promise;
use React\Stream\ReadableStreamInterface;
use function RingCentral\Psr7\stream_for;

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
     * @return RequestBridge
     */
    private function getRequestBridge(): RequestBridge
    {
        return clone $this->bridge;
    }

    /**
     * To run directly the bridge with request without body-entity (like GET request): Any request without
     * Content-Length or Transfer-Encoding headers.
     *
     * @param RequestBridge          $bridge
     * @param ServerRequestInterface $request
     * @param callable               $resolve
     *
     * @return RequestListener
     */
    private function runRequestWithNoBody(
        RequestBridge $bridge,
        ServerRequestInterface $request,
        callable $resolve
    ): RequestListener {
        $bridge->run($request, $resolve);

        return $this;
    }

    /**
     * To register the bridge to be executed on data event to execute a request a body-entity, (like POST request):
     * Any request with Content-Length or Transfer-Encoding headers.
     *
     * @param ReadableStreamInterface $body
     * @param RequestBridge           $bridge
     * @param ServerRequestInterface  $request
     * @param callable                $resolve
     *
     * @return RequestListener
     */
    private function runRequestWithBody(
        ReadableStreamInterface $body,
        RequestBridge $bridge,
        ServerRequestInterface $request,
        callable $resolve
    ): RequestListener {
        $content = '';
        //to concat body value into an unique string, will used as stream
        $body->on('data', function ($data) use ($bridge, $request, $resolve, &$content) {
            $content .= (string) $data;
        });

        //To start symfony loop when the body has been sent
        $body->on('end', function () use ($bridge, $request, $resolve, &$content) {
            //To replace the React Stream instance, not directly usable by Symfony (React Stream does not support
            // __toString(), body is fetchable via events only, but symfony factory use only getContents.
            $request = $request->withBody(stream_for($content));

            if (!$request instanceof ServerRequestInterface) {
                throw new \LogicException('Error the request returned is invalid');
            }

            //To decode the body when it is encoding followinf x form urlencoded
            $parsedBody = [];
            \parse_str($content, $parsedBody);

            if (\is_array($parsedBody)) {
                $request = $request->withParsedBody($parsedBody);
            }

            $bridge->run($request, $resolve);
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
     * @param ServerRequestInterface $request
     *
     * @return Promise
     */
    public function __invoke(ServerRequestInterface $request): Promise
    {
        return new Promise(function ($resolve) use ($request) {
            $bridge = $this->getRequestBridge();

            if ('TRACE' !== \strtoupper($request->getMethod())
                && ($request->hasHeader('Content-Length') || $request->hasHeader('Transfer-Encoding'))) {

                /**
                 * @var ReadableStreamInterface $body
                 */
                $body = $request->getBody();

                return $this->runRequestWithBody($body, $bridge, $request, $resolve);
            } else {
                return $this->runRequestWithNoBody($bridge, $request, $resolve);
            }
        });
    }
}
