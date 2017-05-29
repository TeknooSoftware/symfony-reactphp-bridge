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

namespace Teknoo\ReactPHPBundle\Command;

use React\EventLoop\LoopInterface;
use React\Socket\Server as SocketServer;
use React\Socket\SecureServer as TlsSocketServer;
use React\Http\Server as HttpServer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Teknoo\ReactPHPBundle\Bridge\RequestListener;
use Teknoo\ReactPHPBundle\Logger\StdLogger;

/**
 * Class ReactPHPCommand. Main command controller to interpred CLI command, configure and start ReactPHP to start the
 * Symfony Kernel and handling requests.
 *
 * Options are
 * --interface | -i : To set the TCP interface listened by ReactPHP (by default 0.0.0.0)
 * --port | -p : To set the TCP port listened by ReactPHP (by default 80)
 * --secure | s : To enable TLS support, need to pass a local certificate (by default 0)
 * --local-cert | -l : Locate certificate file for TLS support
 * --env | -e : The Symfony environment to use (by default prod)
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ReactPHPCommand extends ContainerAwareCommand
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
     * ReactPHPCommand constructor.
     *
     * @param RequestListener $requestListener
     * @param LoopInterface   $loop
     * @param StdLogger       $logger
     * @param string|null     $name            The name of the command; passing null means it must be set in configure()
     *
     * @throws \LogicException When the command name is empty
     */
    public function __construct(
        RequestListener $requestListener,
        LoopInterface $loop,
        StdLogger $logger,
        string $name = null
    ) {
        $this->requestListener = $requestListener;
        $this->loop = $loop;
        $this->logger = $logger;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('reactphp:run');
        $this->setDescription('To create easily a ReactPHP Server with Symfony');

        $this->addOption(
            'interface',
            'i',
            InputOption::VALUE_OPTIONAL,
            'To set the TCP interface listened by ReactPHP',
            '0.0.0.0'
        );

        $this->addOption(
            'port',
            'p',
            InputOption::VALUE_OPTIONAL,
            'To set the TCP port listened by ReactPHP',
            '80'
        );

        $this->addOption(
            'secure',
            's',
            InputOption::VALUE_OPTIONAL,
            'To enable TLS support, need to pass a local certificate',
            false
        );

        $this->addOption(
            'local-cert',
            'l',
            InputOption::VALUE_OPTIONAL,
            'Locate certificate file for TLS support',
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //Configure logger
        $this->logger->setStdOutput($output);
        if ($output instanceof ConsoleOutputInterface) {
            $this->logger->setStdError($output->getErrorOutput());
        }

        $listenedInterface = $input->getOption('interface').':'.$input->getOption('port');
        $output->writeln('Start server on '.$listenedInterface);

        //Create front socket server
        $socket = new SocketServer($listenedInterface, $this->loop);

        //Enable TLS socker server to encode/decode requests and responses
        if ($input->getOption('secure')) {
            $localCert = $input->getOption('local-cert');

            if (empty($localCert) || !\file_exists($localCert)) {
                $output->getErrorOutput()->writeln('Error, missing local certificate for secure server');

                return;
            }

            $socket = new TlsSocketServer(
                $socket,
                $this->loop,
                ['local_cert' => $localCert]
            );
        }

        //Enable HTTP server
        $server = new HttpServer($this->requestListener);
        $server->listen($socket);

        $this->loop->run();
    }
}
