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

namespace Teknoo\Tests\ReactPHPBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Teknoo\ReactPHPBundle\ReactPHPBundle;

/**
 * Class ConfigurationTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\ReactPHPBundle
 */
class ReactPHPBundleTest extends \PHPUnit_Framework_TestCase
{
    public function buildBundle()
    {
        return new ReactPHPBundle();
    }

    public function testBuild()
    {
        $container = $this->createMock(ContainerBuilder::class);
        $container->expects(self::atLeastOnce())->method('addCompilerPass');

        $this->buildBundle()->build($container);
    }
}
