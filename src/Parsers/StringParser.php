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
 * @version    0.9.2
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Parsers;

class StringParser {

    /**
     * Returns parsed string where variables in :code format are replaced by the given data array.
     *
     * @param string $content
     * @param array $data
     * @return string
     */
    public static function parse(string $content, array $data = []) : string {
        $map = collect($data)->flatMap(function($item, $key) {
            return [':' . $key => $item];
        });

        foreach($map->all() as $search => $replace) {
            if( ! (is_array($replace) || is_object($replace)) ) {
                $content = str_replace($search, $replace, $content);
            }
        }

        return $content;
    }

}