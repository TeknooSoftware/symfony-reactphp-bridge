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

namespace Teknoo\Tests\ReactPHPBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Teknoo\ReactPHPBundle\DependencyInjection\RequestParserCompilerPass;

/**
 * Class ReactPHPExtensionTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\DependencyInjection\RequestParserCompilerPass
 */
class RequestParserCompilerPassTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @return ContainerBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getContainerBuilderMock()
    {
        if (!$this->container instanceof ContainerBuilder) {
            $this->container = $this->createMock(ContainerBuilder::class);
        }

        return $this->container;
    }

    /**
     * @return RequestParserCompilerPass
     */
    public function buildCompilerPass()
    {
        return new RequestParserCompilerPass();
    }

    public function testProcess()
    {
        $def = $this->createMock(Definition::class);
        $def->expects($this->exactly(2))->method('addMethodCall')->willReturnSelf();

        $this->getContainerBuilderMock()
            ->expects(self::any())
            ->method('findTaggedServiceIds')
            ->with('reactphp_bridge.request_parser')
            ->willReturn([
                'service1' => ['foo' => 'bar'],
                'service2' => ['bar' => 'foo'],
            ]);

        $this->getContainerBuilderMock()
            ->expects(self::once())
            ->method('has')
            ->with('teknoo.reactphp_bridge.request_builder')
            ->willReturn(true);

        $this->getContainerBuilderMock()
            ->expects(self::once())
            ->method('findDefinition')
            ->with('teknoo.reactphp_bridge.request_builder')
            ->willReturn($def);

        self::assertInstanceOf(
            RequestParserCompilerPass::class,
            $this->buildCompilerPass()->process(
                $this->getContainerBuilderMock()
            )
        );
    }

    public function testProcessNoService()
    {
        $this->getContainerBuilderMock()
            ->expects(self::never())
            ->method('findTaggedServiceIds');

        $this->getContainerBuilderMock()
            ->expects(self::once())
            ->method('has')
            ->with('teknoo.reactphp_bridge.request_builder')
            ->willReturn(false);

        $this->getContainerBuilderMock()
            ->expects(self::never())
            ->method('findDefinition');

        self::assertInstanceOf(
            RequestParserCompilerPass::class,
            $this->buildCompilerPass()->process(
                $this->getContainerBuilderMock()
            )
        );
    }

    /**
     * @expectedException \TypeError
     */
    public function testProcessError()
    {
        $this->buildCompilerPass()->process(new \stdClass());
    }
}
