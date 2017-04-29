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

namespace Teknoo\ReactPHPBundle\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class StdLogger.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class StdLogger extends AbstractLogger
{
    /**
     * @var OutputInterface
     */
    private $stdOutput;

    /**
     * @var OutputInterface
     */
    private $stdError;

    /**
     * @param OutputInterface $stdOutput
     *
     * @return self
     */
    public function setStdOutput(OutputInterface $stdOutput): StdLogger
    {
        $this->stdOutput = $stdOutput;

        return $this;
    }

    /**
     * @param OutputInterface $stdError
     *
     * @return self
     */
    public function setStdError(OutputInterface $stdError): StdLogger
    {
        $this->stdError = $stdError;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD)
     */
    public function log($level, $message, array $context = array())
    {
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
            case LogLevel::ERROR:
            case LogLevel::WARNING:
                if ($this->stdError instanceof OutputInterface) {
                    $this->stdError->writeln($message);
                }
                break;
            case LogLevel::NOTICE:
            case LogLevel::INFO:
            case LogLevel::DEBUG:
                if ($this->stdOutput instanceof OutputInterface) {
                    $this->stdOutput->writeln($message);
                }
                break;
        }

        return $this;
    }
}
