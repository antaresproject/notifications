<?php

namespace Antares\Notifications\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\TransformsRequest;

class TransformBoolean extends TransformsRequest
{

    /**
     * Transform the given value.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return mixed
     */
    protected function transform($key, $value) {
        if($value === 'true') {
            return true;
        }

        if($value === 'false') {
            return false;
        }

        return $value;
    }

}