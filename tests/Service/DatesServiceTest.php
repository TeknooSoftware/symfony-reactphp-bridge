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

namespace Teknoo\Tests\ReactPHPBundle\Service;

use Teknoo\ReactPHPBundle\Service\DatesService;

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
 * @covers \Teknoo\ReactPHPBundle\Service\DatesService
 */
class DatesServiceTest extends \PHPUnit_Framework_TestCase
{
    public function buildService()
    {
        return new DatesService();
    }

    public function testSetBehavior()
    {
        $date = new \DateTime('2017-04-27 10:09:08');

        $service = $this->buildService();
        self::assertInstanceOf(DatesService::class, $service->setNow($date));
        self::assertEquals($date, $service->getNow());
        self::assertEquals($date, $service->getNow());
    }

    public function testGetWithoutSetBehavior()
    {
        $service = $this->buildService();
        $a = $service->getNow();
        self::assertInstanceOf(\DateTime::class, $a);
        sleep(2);
        $b = $service->getNow();
        self::assertInstanceOf(\DateTime::class, $b);
        self::assertNotEquals($a, $b);
        self::assertGreaterThan($a, $b);
    }
}