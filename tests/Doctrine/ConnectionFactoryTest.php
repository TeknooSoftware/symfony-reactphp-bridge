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

use Doctrine\DBAL\Driver;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Teknoo\ReactPHPBundle\Doctrine\ConnectionFactory;

/**
 * Class ConnectionFactoryTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\Doctrine\ConnectionFactory
 */
class ConnectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function buildFactory()
    {
        return new ConnectionFactory([]);
    }

    public function testCreateConnection()
    {
        $factory = $this->buildFactory();

        $this->getMockBuilder(Driver::class)->setMockClassName('ConnectionMock')->getMock();

        $connection1 = $factory->createConnection([
            'driverClass' => 'ConnectionMock',
            'platform' => $this->createMock(AbstractPlatform::class),
        ]);

        $connection2 = $factory->createConnection([
            'driverClass' => 'ConnectionMock',
            'platform' => $this->createMock(AbstractPlatform::class),
        ]);

        self::assertSame($connection1, $connection2);
    }
}
