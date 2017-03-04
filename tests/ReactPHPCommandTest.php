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

namespace Teknoo\Tests\ReactPHP\Symfony;

use React\EventLoop\LoopInterface;
use React\Http\Request;
use React\Http\Response;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Teknoo\ReactPHP\Symfony\ReactPHPCommand;
use Teknoo\ReactPHP\Symfony\RequestBridge;

/**
 * Class RequestBridgeTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHP\Symfony\ReactPHPCommand
 */
class ReactPHPCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @return KernelInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getKernel(): KernelInterface
    {
        if (!$this->kernel instanceof KernelInterface) {
            $this->kernel = $this->createMock(KernelInterface::class);
        }

        return $this->kernel;
    }

    /**
     * @return LoopInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLoop(): LoopInterface
    {
        if (!$this->loop instanceof LoopInterface) {
            $this->loop = $this->createMock(LoopInterface::class);
        }

        return $this->loop;
    }

    public function buildCommand()
    {
        return new ReactPHPCommand($this->getKernel(), $this->loop);
    }

    public function testRun()
    {
        $input = $this->createMock(InputInterface::class);
        $input->expects(self::any())
            ->method('getOption')
            ->willReturnCallback(function ($name) {
                switch ($name) {
                    case 'interface':
                        return '0.0.0.0';
                        break;
                    case 'port':
                        return '8012';
                        break;
                }

                return '';
            });

        $this->getLoop()->expects(self::once())->method('run');

        $this->buildCommand()->run($input, $this->createMock(OutputInterface::class));
    }
}