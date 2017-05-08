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

namespace Teknoo\ReactPHPBundle\Bridge\Parser;

use React\Http\Request as ReactRequest;
use Teknoo\ReactPHPBundle\Bridge\RequestBuilder;

/**
 * Class ServerRequestParser.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 */
class ServerRequestParser implements RequestParserInterface
{
    /**
     * {@inheritdoc}
     */
    public function parse(ReactRequest $request, RequestBuilder $builder): RequestParserInterface
    {
        $server = \array_merge(
            $_SERVER,
            [
                'REQUEST_URI' => $request->getPath(),
                'REMOTE_ADDR' => $request->remoteAddress,
            ]
        );

        $headers = $request->getHeaders();

        if (isset($headers['Host'][0])) {
            $server['SERVER_NAME'] = \explode(':', $headers['Host'][0]);
        }

        $builder->setServer($server);

        return $this;
    }
}