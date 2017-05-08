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
use Teknoo\ReactPHPBundle\Bridge\Parser\RequestParserInterface;
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
     * Request's HTTP method.
     *
     * @var string
     */
    private $method = '';

    /**
     * Request's HTTP headers.
     *
     * @var array
     */
    private $header = [];

    /**
     * Request's HTTP query part (like $_GET).
     *
     * @var array
     */
    private $query = [];

    /**
     * Request's HTTP body part (like $_POST).
     *
     * @var array
     */
    private $requestParsed = [];

    /**
     * Symfony's kernel attribute.
     *
     * @var array
     */
    private $attributes = [];

    /**
     * Request's HTTP Cookies (like $_COOKIES).
     *
     * @var array
     */
    private $cookies = [];

    /**
     * Request's HTTP Files transmitted (like $_FILES).
     *
     * @var array
     */
    private $files = [];

    /**
     * $_SERVER implementation for Symfony.
     *
     * @var array
     */
    private $server = [];

    /**
     * Body content.
     *
     * @var null
     */
    private $content = null;

    /**
     * List of parser to configure the builder before create Symfony request.
     *
     * @var RequestParserInterface[]
     */
    private $requestParsersList = [];

    /**
     * RequestBuilder constructor.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    /**
     * @param RequestParserInterface $requestParser
     *
     * @return RequestBuilder
     */
    public function registerRequestParser(RequestParserInterface $requestParser): RequestBuilder
    {
        $this->requestParsersList[\get_class($requestParser)] = $requestParser;

        return $this;
    }

    /**
     * Get the Request's HTTP method.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * Set the Request's HTTP method.
     *
     * @param string $method
     *
     * @return self
     */
    public function setMethod(string $method): RequestBuilder
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Get the Request's HTTP headers.
     *
     * @return array
     */
    public function getHeader(): array
    {
        return $this->header;
    }

    /**
     * Set the Request's HTTP headers.
     *
     * @param array $header
     *
     * @return self
     */
    public function setHeader(array $header): RequestBuilder
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Get the Request's HTTP query part (like $_GET).
     *
     * @return array
     */
    public function getQuery(): array
    {
        return $this->query;
    }

    /**
     * Set the Request's HTTP query part (like $_GET).
     *
     * @param array $query
     *
     * @return self
     */
    public function setQuery(array $query): RequestBuilder
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get the Request's HTTP body part (like $_POST).
     *
     * @return array
     */
    public function getRequestParsed(): array
    {
        return $this->requestParsed;
    }

    /**
     * Set the Request's HTTP body part (like $_POST).
     *
     * @param array $requestParsed
     *
     * @return self
     */
    public function setRequestParsed(array $requestParsed): RequestBuilder
    {
        $this->requestParsed = $requestParsed;

        return $this;
    }

    /**
     * Get the Symfony's kernel attribute.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Set the Symfony's kernel attribute.
     *
     * @param array $attributes
     *
     * @return self
     */
    public function setAttributes(array $attributes): RequestBuilder
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Get the Request's HTTP Cookies (like $_COOKIES).
     *
     * @return array
     */
    public function getCookies(): array
    {
        return $this->cookies;
    }

    /**
     * Set the Request's HTTP Cookies (like $_COOKIES).
     *
     * @param array $cookies
     *
     * @return self
     */
    public function setCookies(array $cookies): RequestBuilder
    {
        $this->cookies = $cookies;

        return $this;
    }

    /**
     * Get the Request's HTTP Files transmitted (like $_FILES).
     *
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Set the Request's HTTP Files transmitted (like $_FILES).
     *
     * @param array $files
     *
     * @return self
     */
    public function setFiles(array $files): RequestBuilder
    {
        $this->files = $files;

        return $this;
    }

    /**
     * Get the $_SERVER implementation for Symfony.
     *
     * @return array
     */
    public function getServer(): array
    {
        return $this->server;
    }

    /**
     * Set the $_SERVER implementation for Symfony.
     *
     * @param array $server
     *
     * @return self
     */
    public function setServer(array $server): RequestBuilder
    {
        $this->server = $server;

        return $this;
    }

    /**
     * Get the Body content.
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set the Body content.
     *
     * @param null $content
     *
     * @return self
     */
    public function setContent($content): RequestBuilder
    {
        $this->content = $content;

        return $this;
    }

    /**
     * To prepare the builder to create a new Symfony Request.
     */
    private function cleanBuilder()
    {
        $this->method = '';
        $this->query = [];
        $this->header = [];
        $this->requestParsed = [];
        $this->cookies = [];
        $this->files = [];
        $this->server = [];
        $this->content = null;
    }

    /**
     * To prepare the builder to create a new Symfony Request and lost preivous cloning data.
     */
    public function __clone()
    {
        $this->cleanBuilder();
    }

    /**
     * To create the Symfony Request after parsers execution, from data exracted into this builder.
     *
     * @return SymfonyRequest
     */
    protected function getSymfonyRequest(): SymfonyRequest
    {
        $sfRequest = new SymfonyRequest(
            $this->getQuery(),
            $this->getRequestParsed(),
            $this->getAttributes(),
            $this->getCookies(),
            $this->getFiles(),
            $this->getServer(),
            $this->getContent()
        );

        $sfRequest->setMethod($this->getMethod());
        $sfRequest->headers->replace($this->getHeader());

        return $sfRequest;
    }

    /**
     * Called by Request Bridge to transform the ReactPHP Request to a usefull Symfony request, using referenced parser.
     *
     * @param ReactRequest  $request
     * @param RequestBridge $bridge
     *
     * @return RequestBuilder
     */
    public function buildRequest(ReactRequest $request, RequestBridge $bridge): RequestBuilder
    {
        foreach ($this->requestParsersList as $requestParser) {
            $requestParser->parse($request, $this);
        }

        $bridge->executePreparedRequest($this->getSymfonyRequest());

        return $this;
    }
}
