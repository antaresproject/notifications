<?php

/**
 * Part of the Antares package.
 *
 * NOTICE OF LICENSE
 *
 * Licensed under the 3-clause BSD License.
 *
 * This source file is subject to the 3-clause BSD License that is
 * bundled with this package in the LICENSE file.
 *
 * @package    Notifications
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Parsers\TestCase;

use Antares\Notifications\Parsers\StringParser;
use PHPUnit\Framework\TestCase;

class StringParserTest extends TestCase
{

    /**
     * @dataProvider dataProvider
     *
     * @param string $content
     * @param array $data
     * @param string $parsed
     */
    public function testParser(string $content, string $parsed, array $data) {
        $this->assertEquals($parsed, StringParser::parse($content, $data));
    }

    /**
     * @return array
     */
    public function dataProvider() {
        return [
            [
                'clean text',
                'clean text',
                [],
            ],
            [
                'A title with dummy :aaa variable',
                'A title with dummy :aaa variable',
                [],
            ],
            [
                'A title with :aaa variable',
                'A title with extra variable',
                ['aaa' => 'extra'],
            ],
            [
                'A title with :aaa variable and another :bbb',
                'A title with extra variable and another variable',
                ['aaa' => 'extra', 'bbb' => 'variable'],
            ],
            [
                'A title with :namespace::aaa variable',
                'A title with namespaced variable',
                ['namespace::aaa' => 'namespaced'],
            ],
        ];
    }

}