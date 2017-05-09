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

use Teknoo\ReactPHPBundle\Bridge\Parser\CookiesRequestParser;
use React\Http\Request as ReactRequest;
use Teknoo\ReactPHPBundle\Bridge\RequestBuilder;

/**
 * Class CookiesRequestParserTest.
 *
 * @copyright   Copyright (c) 2009-2017 Richard Déloge (richarddeloge@gmail.com)
 *
 * @link        http://teknoo.software/symfony-react Project website
 *
 * @license     http://teknoo.software/license/mit         MIT License
 * @author      Richard Déloge <richarddeloge@gmail.com>
 *
 * @covers \Teknoo\ReactPHPBundle\Bridge\Parser\CookiesRequestParser
 */
class CookiesRequestParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return CookiesRequestParser
     */
    public function buildParser(): CookiesRequestParser
    {
        return new CookiesRequestParser();
    }

    public function testParseWithoutCookies()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')->willReturn(['foo' => 'Bar']);
        $builder->expects(self::never())->method('setCookies');

        self::assertInstanceOf(CookiesRequestParser::class, $this->buildParser()->parse($request, $builder));
    }

    public function testParseWithCookies()
    {
        $request = $this->createMock(ReactRequest::class);
        $builder = $this->createMock(RequestBuilder::class);

        $request->expects(self::once())->method('getHeaders')
            ->willReturn([
                'Cookie' => [
                    'foo=bar; bar=foo',
                    'hello=world;'
                ]
            ]);

        $builder->expects(self::once())
            ->method('setCookies')
            ->with([
                'foo' => 'bar',
                'bar' => 'foo',
                'hello' => 'world'
            ])
            ->willReturnSelf();

        self::assertInstanceOf(CookiesRequestParser::class, $this->buildParser()->parse($request, $builder));
    }
}