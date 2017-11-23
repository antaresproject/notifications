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

namespace Antares\Notifications\Model;

use Illuminate\Contracts\Support\Arrayable;
use Closure;

class Recipient implements Arrayable {

    /**
     * Area name of recipient.
     *
     * @var string
     */
    protected $area;

    /**
     * Closure which will resolve recipient.
     *
     * @var Closure
     */
    protected $resolver;

    /**
     * Recipient constructor.
     * @param string $area
     * @param Closure $resolver
     */
    public function __construct(string $area, Closure $resolver) {
        $this->area     = $area;
        $this->resolver = $resolver;
    }

    /**
     * Returns area name.
     *
     * @return string
     */
    public function getArea() : string {
        return $this->area;
    }

    /**
     * Returns label from area name.
     *
     * @return string
     */
    public function getLabel() : string {
        return ucfirst($this->area);
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray() : array {
        return [
            'area'  => $this->getArea(),
            'label' => $this->getLabel(),
        ];
    }

    /**
     * Resolve recipient with the given event object.
     *
     * @param $event
     * @return mixed
     */
    public function resolve($event) {
        return call_user_func($this->resolver, $event);
    }

}