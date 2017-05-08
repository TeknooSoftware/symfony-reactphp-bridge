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

/**
 * Class AbstractContentTypeRequestParser.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @SuppressWarnings(PHPMD)
 */
abstract class AbstractContentTypeRequestParser
{
    /**
     * @var array
     */
    protected static $supportedContentsTypes = [];

    /**
     * {@inheritdoc}
     */
    protected function supportsContentType(ReactRequest $request): bool
    {
        $headers = $request->getHeaders();

        $contentType = '';
        if (isset($headers['Content-Type'])) {
            $contentType = \implode(' ', (array) $headers['Content-Type']);
        }

        foreach (static::$supportedContentsTypes as $supportedContentType) {
            if (false !== \strpos(\strtolower($contentType), $supportedContentType)) {
                return true;
            }
        }

        return false;
    }
}
