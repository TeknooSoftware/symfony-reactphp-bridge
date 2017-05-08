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

use React\EventLoop\LoopInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\ReactPHPBundle\Bridge\RequestListener;
use Teknoo\ReactPHPBundle\Command\ReactPHPCommand;
use Teknoo\ReactPHPBundle\Logger\StdLogger;

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
 * @covers \Teknoo\ReactPHPBundle\Command\ReactPHPCommand
 */
class ReactPHPCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RequestListener
     */
    private $requestListener;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * @var StdLogger
     */
    private $logger;

    /**
     * @return RequestListener|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getRequestListener(): RequestListener
    {
        if (!$this->requestListener instanceof RequestListener) {
            $this->requestListener = $this->createMock(RequestListener::class);
        }

        return $this->requestListener;
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

    /**
     * @return StdLogger|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getLogger(): StdLogger
    {
        if (!$this->logger instanceof StdLogger) {
            $this->logger = $this->createMock(StdLogger::class);
        }

        return $this->logger;
    }

    public function buildCommand()
    {
        return new ReactPHPCommand($this->getRequestListener(), $this->getLoop(), $this->getLogger());
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

        $output = $this->createMock(OutputInterface::class);

        $this->getLogger()->expects(self::once())->method('setStdOutput')->with($output);
        $this->getLogger()->expects(self::never())->method('setStdError')->with($output);

        $this->buildCommand()->run($input, $output);
    }

    public function testRunWithError()
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

        $error = $this->createMock(OutputInterface::class);
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::any())->method('getErrorOutput')->willReturn($error);

        $this->getLogger()->expects(self::once())->method('setStdOutput')->with($output);
        $this->getLogger()->expects(self::once())->method('setStdError')->with($error);

        $this->buildCommand()->run($input, $output);
    }

    public function testRunTlsWithoutCertificate()
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
                    case 'secure':
                        return true;
                        break;
                }

                return '';
            });

        $this->getLoop()->expects(self::never())->method('run');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::once())->method('writeln');
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::any())->method('getErrorOutput')->willReturn($error);

        $this->getLogger()->expects(self::once())->method('setStdOutput')->with($output);
        $this->getLogger()->expects(self::once())->method('setStdError')->with($error);

        $this->buildCommand()->run($input, $output);
    }
    public function testRunTlsCertificate()
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
                    case 'secure':
                        return true;
                        break;
                    case 'local-cert':
                        return '/dev/random';
                        break;
                }

                return '';
            });

        $this->getLoop()->expects(self::once())->method('run');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::never())->method('writeln');
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::any())->method('getErrorOutput')->willReturn($error);

        $this->getLogger()->expects(self::once())->method('setStdOutput')->with($output);
        $this->getLogger()->expects(self::once())->method('setStdError')->with($error);

        $this->buildCommand()->run($input, $output);
    }
}
