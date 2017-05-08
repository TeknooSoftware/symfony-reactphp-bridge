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
use Symfony\Component\HttpFoundation\Request;
use Teknoo\ReactPHPBundle\Bridge\Parser\RequestParserInterface;
use Teknoo\ReactPHPBundle\Bridge\RequestBridge;
use Teknoo\ReactPHPBundle\Bridge\RequestBuilder;

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
 * @covers \Teknoo\ReactPHPBundle\Bridge\RequestBuilder
 */
class RequestBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $arguments
     * @return RequestBuilder
     */
    public function buildBuilder($arguments = []): RequestBuilder
    {
        return new RequestBuilder($arguments);
    }

    public function testRegisterRequestParser()
    {
        self::assertInstanceOf(
            RequestBuilder::class,
            $this->buildBuilder()->registerRequestParser($this->createMock(RequestParserInterface::class))
        );
    }

    public function testMethod()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setMethod('GET'));
        self::assertEquals('GET', $builder->getMethod());
    }

    public function testHeader()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setHeader(['foo'=>'bar']));
        self::assertEquals(['foo'=>'bar'], $builder->getHeader());
    }

    public function testQuery()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setQuery(['foo'=>'bar']));
        self::assertEquals(['foo'=>'bar'], $builder->getQuery());
    }

    public function testRequestParsed()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setRequestParsed(['foo'=>'bar']));
        self::assertEquals(['foo'=>'bar'], $builder->getRequestParsed());
    }

    public function testAttributes()
    {
        $builder = $this->buildBuilder(['bar'=>'foo']);
        self::assertEquals(['bar'=>'foo'], $builder->getAttributes());
        self::assertInstanceOf(RequestBuilder::class, $builder->setAttributes(['foo'=>'bar']));
        self::assertEquals(['foo'=>'bar'], $builder->getAttributes());
    }

    public function testCookies()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setCookies(['foo'=>'bar']));
        self::assertEquals(['foo'=>'bar'], $builder->getCookies());
    }

    public function testFiles()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setFiles(['foo'=>'bar']));
        self::assertEquals(['foo'=>'bar'], $builder->getFiles());
    }

    public function testServer()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setServer(['foo'=>'bar']));
        self::assertEquals(['foo'=>'bar'], $builder->getServer());
    }

    public function testContent()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setContent('fooBar'));
        self::assertEquals('fooBar', $builder->getContent());
    }

    public function testClone()
    {
        $builder = $this->buildBuilder();
        self::assertInstanceOf(RequestBuilder::class, $builder->setMethod('GET'));
        self::assertInstanceOf(RequestBuilder::class, $builder->setHeader(['foo'=>'bar']));
        self::assertInstanceOf(RequestBuilder::class, $builder->setQuery(['foo'=>'bar']));
        self::assertInstanceOf(RequestBuilder::class, $builder->setRequestParsed(['foo'=>'bar']));
        self::assertInstanceOf(RequestBuilder::class, $builder->setAttributes(['foo'=>'bar']));
        self::assertInstanceOf(RequestBuilder::class, $builder->setCookies(['foo'=>'bar']));
        self::assertInstanceOf(RequestBuilder::class, $builder->setFiles(['foo'=>'bar']));
        self::assertInstanceOf(RequestBuilder::class, $builder->setServer(['foo'=>'bar']));
        self::assertInstanceOf(RequestBuilder::class, $builder->setContent('fooBar'));

        $clonedBuilder = clone $builder;

        self::assertEquals('GET', $builder->getMethod());
        self::assertEquals(['foo'=>'bar'], $builder->getHeader());
        self::assertEquals(['foo'=>'bar'], $builder->getQuery());
        self::assertEquals(['foo'=>'bar'], $builder->getRequestParsed());
        self::assertEquals(['foo'=>'bar'], $builder->getAttributes());
        self::assertEquals(['foo'=>'bar'], $builder->getCookies());
        self::assertEquals(['foo'=>'bar'], $builder->getFiles());
        self::assertEquals(['foo'=>'bar'], $builder->getServer());
        self::assertEquals('fooBar', $builder->getContent());

        self::assertEmpty($clonedBuilder->getMethod());
        self::assertEmpty($clonedBuilder->getHeader());
        self::assertEmpty($clonedBuilder->getQuery());
        self::assertEmpty($clonedBuilder->getRequestParsed());
        self::assertEquals(['foo'=>'bar'], $clonedBuilder->getAttributes());
        self::assertEmpty($clonedBuilder->getCookies());
        self::assertEmpty($clonedBuilder->getFiles());
        self::assertEmpty($clonedBuilder->getServer());
        self::assertEmpty($clonedBuilder->getContent());
    }

    public function testBuildRequest()
    {
        $builder = $this->buildBuilder();
        $request = $this->createMock(\React\Http\Request::class);
        $bridge = $this->createMock(RequestBridge::class);

        $parser1 = $this->getMockBuilder(RequestParserInterface::class)->setMockClassName('Parser1')->getMock();
        $parser1->expects(self::once())->method('parse')->with($request, $builder);
        $parser2 = $this->getMockBuilder(RequestParserInterface::class)->setMockClassName('Parser2')->getMock();
        $parser2->expects(self::never())->method('parse')->with($request, $builder);
        $parser3 = $this->getMockBuilder(RequestParserInterface::class)->setMockClassName('Parser2')->getMock();
        $parser3->expects(self::once())->method('parse')->with($request, $builder);

        $bridge->expects(self::once())
            ->method('executePreparedRequest')
            ->with($this->callback(function ($req) { return $req instanceof Request;}));

        self::assertInstanceOf(
            RequestBuilder::class,
            $builder->registerRequestParser($parser1)
        );

        self::assertInstanceOf(
            RequestBuilder::class,
            $builder->registerRequestParser($parser2)
        );

        self::assertInstanceOf(
            RequestBuilder::class,
            $builder->registerRequestParser($parser3)
        );

        self::assertInstanceOf(
            RequestBuilder::class,
            $builder->buildRequest(
                $request,
                $bridge
            )
        );
    }
}