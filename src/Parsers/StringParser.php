<?php

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