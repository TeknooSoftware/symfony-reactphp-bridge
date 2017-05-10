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

use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * Class RequestBuilder. Builder, managing request's parsers to generate a usefull Symfony request from
 * ReactPHP Request.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class RequestBuilder
{
    /**
     * @var HttpFoundationFactory
     */
    private $factory;

    /**
     * RequestBuilder constructor.
     * @param HttpFoundationFactory $factory
     */
    public function __construct(HttpFoundationFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * To create the Symfony Request after parsers execution, from data exracted into this builder.
     * @param ServerRequestInterface $request
     * @return SymfonyRequest
     */
    protected function getSymfonyRequest(ServerRequestInterface $request): SymfonyRequest
    {
        return $this->factory->createRequest($request);
    }

    /**
     * Called by Request Bridge to transform the ReactPHP Request to a usefull Symfony request, using referenced parser.
     *
     * @param ServerRequestInterface  $request
     * @param RequestBridge $bridge
     *
     * @return RequestBuilder
     */
    public function buildRequest(ServerRequestInterface $request, RequestBridge $bridge): RequestBuilder
    {
        $bridge->executePreparedRequest($this->getSymfonyRequest($request));

        return $this;
    }
}
