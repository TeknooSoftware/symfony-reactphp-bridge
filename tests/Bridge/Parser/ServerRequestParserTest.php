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

namespace Teknoo\Tests\ReactPHPBundle\Bridge\Parser;

use Teknoo\ReactPHPBundle\Bridge\Parser\ServerRequestParser;
use React\Http\Request as ReactRequest;
use Teknoo\ReactPHPBundle\Bridge\RequestBuilder;

/**
 * Class ServerRequestParserTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\Bridge\Parser\ServerRequestParser
 */
class ServerRequestParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return ServerRequestParser
     */
    public function buildParser(): ServerRequestParser
    {
        return new ServerRequestParser();
    }

    public function testParse()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->remoteAddress = '123.123.123.123';
        $request->expects(self::any())->method('getPath')->willReturn('/foo');
        $request->expects(self::any())->method('getHeaders')->willReturn(['Host' => ['foo.bar']]);
        $builder->expects(self::once())->method('setServer');

        self::assertInstanceOf(ServerRequestParser::class, $this->buildParser()->parse($request, $builder));
    }
}
