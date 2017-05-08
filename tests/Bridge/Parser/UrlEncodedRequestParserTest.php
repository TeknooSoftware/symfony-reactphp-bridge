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

namespace Teknoo\Tests\ReactPHPBundle\Bridge\Parser;

use Teknoo\ReactPHPBundle\Bridge\Parser\UrlEncodedRequestParser;
use React\Http\Request as ReactRequest;
use Teknoo\ReactPHPBundle\Bridge\RequestBuilder;

/**
 * Class UrlEncodeRequestParserTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\Bridge\Parser\UrlEncodedRequestParser
 * @covers \Teknoo\ReactPHPBundle\Bridge\Parser\AbstractContentTypeRequestParser
 */
class UrlEncodedRequestParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return UrlEncodedRequestParser
     */
    public function buildParser(): UrlEncodedRequestParser
    {
        return new UrlEncodedRequestParser();
    }

    public function testParseJson()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')->willReturn(['Content-Type' => ['application/json']]);
        $builder->expects(self::never())->method('setRequestParsed');

        self::assertInstanceOf(UrlEncodedRequestParser::class, $this->buildParser()->parse($request, $builder));
    }

    public function testParseJavascript()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')->willReturn(['Content-Type' => ['application/javascript']]);
        $builder->expects(self::never())->method('setRequestParsed');

        self::assertInstanceOf(UrlEncodedRequestParser::class, $this->buildParser()->parse($request, $builder));
    }

    public function testParseLdJson()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')->willReturn(['Content-Type' => ['application/ld+json']]);
        $builder->expects(self::never())->method('setRequestParsed');

        self::assertInstanceOf(UrlEncodedRequestParser::class, $this->buildParser()->parse($request, $builder));
    }

    public function testParseMultipart()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')->willReturn(['Content-Type' => ['multipart/form-data']]);
        $builder->expects(self::never())->method('setRequestParsed');

        self::assertInstanceOf(UrlEncodedRequestParser::class, $this->buildParser()->parse($request, $builder));
    }

    public function testParseUrlEmpty()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')->willReturn(['Content-Type' => ['application/x-www-form-urlencoded']]);
        $builder->expects(self::once())->method('setRequestParsed')->with([]);

        self::assertInstanceOf(UrlEncodedRequestParser::class, $this->buildParser()->parse($request, $builder));
    }

    public function testParseUrl()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')->willReturn(['Content-Type' => ['application/x-www-form-urlencoded']]);
        $builder->expects(self::any())->method('getContent')->willReturn(\http_build_query(['foo' => 'bar']));
        $builder->expects(self::once())->method('setRequestParsed')->with(['foo' => 'bar']);

        self::assertInstanceOf(UrlEncodedRequestParser::class, $this->buildParser()->parse($request, $builder));
    }
}
