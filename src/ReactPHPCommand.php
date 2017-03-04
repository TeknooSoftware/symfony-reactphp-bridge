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

namespace Teknoo\ReactPHP\Symfony;

use React\EventLoop\LoopInterface;
use React\Socket\Server as SocketServer;
use React\Http\Server as HttpServer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ReactPHPCommand
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ReactPHPCommand extends Command
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
     * ReactPHPCommand constructor.
     * @param KernelInterface $kernel
     * @param LoopInterface $loop
     * @param string|null $name The name of the command; passing null means it must be set in configure()
     *
     * @throws \LogicException When the command name is empty
     */
    public function __construct(KernelInterface $kernel, LoopInterface $loop, string $name = null)
    {
        $this->kernel = $kernel;
        $this->loop = $loop;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('reactphp:run');
        $this->setDescription('To create easily a ReactPHP Server with Symfony');
        $this->addOption('interface', 'i', InputOption::VALUE_OPTIONAL, 'To set the TCP interface listened by ReactPHP', '0.0.0.0');
        $this->addOption('port', 'p', InputOption::VALUE_OPTIONAL, 'To set the TCP port listened by ReactPHP', '80');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $requestBridge = new RequestBridge($this->kernel);
        $requestListener = new RequestListener($requestBridge);

        $listenedInterface = $input->getOption('interface').':'.$input->getOption('port');
        $socket = new SocketServer($listenedInterface, $this->loop);
        $http = new HttpServer($socket);

        $http->on('request', $requestListener);
        $this->loop->run();
    }
}