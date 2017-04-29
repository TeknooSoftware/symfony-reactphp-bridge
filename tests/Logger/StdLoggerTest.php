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

use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
 * @covers \Teknoo\ReactPHPBundle\Logger\StdLogger
 */
class StdLoggerTest extends \PHPUnit_Framework_TestCase
{
    public function buildLogger()
    {
        return new StdLogger();
    }

    public function testSetStdOutput()
    {
        self::assertInstanceOf(
            StdLogger::class,
            $this->buildLogger()->setStdOutput($this->createMock(ConsoleOutputInterface::class))
        );
    }

    public function testSetStdError()
    {
        self::assertInstanceOf(
            StdLogger::class,
            $this->buildLogger()->setStdError($this->createMock(OutputInterface::class))
        );
    }

    public function testLogWithoutOutput()
    {
        $this->buildLogger()->log(LogLevel::EMERGENCY, 'fooBar');
        $this->buildLogger()->log(LogLevel::ALERT, 'fooBar');
        $this->buildLogger()->log(LogLevel::CRITICAL, 'fooBar');
        $this->buildLogger()->log(LogLevel::ERROR, 'fooBar');
        $this->buildLogger()->log(LogLevel::WARNING, 'fooBar');
        $this->buildLogger()->log(LogLevel::NOTICE, 'fooBar');
        $this->buildLogger()->log(LogLevel::INFO, 'fooBar');
        $this->buildLogger()->log(LogLevel::DEBUG, 'fooBar');
    }

    public function testLogEmergency()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::never())->method('writeln');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::once())->method('writeln')->with('fooBar');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::EMERGENCY, 'fooBar');
    }

    public function testLogAlert()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::never())->method('writeln');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::once())->method('writeln')->with('fooBar');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::ALERT, 'fooBar');
    }

    public function testLogCritical()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::never())->method('writeln');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::once())->method('writeln')->with('fooBar');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::CRITICAL, 'fooBar');
    }

    public function testLogError()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::never())->method('writeln');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::once())->method('writeln')->with('fooBar');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::ERROR, 'fooBar');
    }

    public function testLogWarning()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::never())->method('writeln');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::once())->method('writeln')->with('fooBar');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::WARNING, 'fooBar');
    }

    public function testLogNotice()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::once())->method('writeln')->with('fooBar');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::never())->method('writeln');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::NOTICE, 'fooBar');
    }

    public function testLogInfo()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::once())->method('writeln')->with('fooBar');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::never())->method('writeln');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::INFO, 'fooBar');
    }

    public function testLogDebug()
    {
        $output = $this->createMock(ConsoleOutputInterface::class);
        $output->expects(self::once())->method('writeln')->with('fooBar');

        $error = $this->createMock(OutputInterface::class);
        $error->expects(self::never())->method('writeln');

        $logger = $this->buildLogger();

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdOutput($output)
        );

        self::assertInstanceOf(
            StdLogger::class,
            $logger->setStdError($error)
        );

        $logger->log(LogLevel::DEBUG, 'fooBar');
    }
}