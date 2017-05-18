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

namespace Teknoo\ReactPHPBundle\Doctrine;

use Doctrine\Bundle\DoctrineBundle\ConnectionFactory as DoctrineConnectionFactory;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;

/**
 * To share a doctrine connection between each ReactPHP request :
 * - Doctrine connection are not already close at end, to avoid multiple connections to SQL
 * - Allow persistent connection with ReactPHP HTTP. *.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ConnectionFactory extends DoctrineConnectionFactory
{
    /**
     * @var Connection[]
     */
    protected static $connections = [];

    /**
     * @param array $params
     *
     * @return string
     */
    protected function computeParamsash(array &$params): string
    {
        return \hash('sha1', \json_encode($params));
    }

    /**
     * {@inheritdoc}
     */
    public function createConnection(
        array $params,
        Configuration $config = null,
        EventManager $eventManager = null,
        array $mappingTypes = array()
    ) {
        $hash = $this->computeParamsash($params);
        if (!isset(self::$connections[$hash])) {
            self::$connections[$hash] = parent::createConnection($params, $config, $eventManager, $mappingTypes);
        }

        return self::$connections[$hash];
    }
}
