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

use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\HttpFoundationFactory;
use Symfony\Component\HttpFoundation\Request;
use Teknoo\ReactPHPBundle\Bridge\Parser\RequestParserInterface;
use Teknoo\ReactPHPBundle\Bridge\RequestBridge;
use Teknoo\ReactPHPBundle\Bridge\RequestBuilder;

/**
 * Class RequestBuilderTest.
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
     * @var HttpFoundationFactory
     */
    private $factory;

    /**
     * @return HttpFoundationFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getFactory(): HttpFoundationFactory
    {
        if (!$this->factory instanceof HttpFoundationFactory) {
            $this->factory = $this->createMock(HttpFoundationFactory::class);
        }

        return $this->factory;
    }


    /**
     * @return RequestBuilder
     */
    public function buildBuilder(): RequestBuilder
    {
        return new RequestBuilder($this->getFactory());
    }

    public function testBuildRequest()
    {
        $builder = $this->buildBuilder();
        $request = $this->createMock(ServerRequestInterface::class);
        $bridge = $this->createMock(RequestBridge::class);

        $bridge->expects(self::once())
            ->method('executePreparedRequest')
            ->with($this->callback(function ($req) {
                return $req instanceof Request;
            }));

        $this->getFactory()
            ->expects(self::once())
            ->method('createRequest')
            ->with($request)
            ->willReturn($this->createMock(Request::class));

        self::assertInstanceOf(
            RequestBuilder::class,
            $builder->buildRequest(
                $request,
                $bridge
            )
        );
    }
}
