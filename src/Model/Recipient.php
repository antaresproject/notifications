<?php

namespace Antares\Notifications\Model;

use Illuminate\Contracts\Support\Arrayable;
use Closure;

class Recipient implements Arrayable {

    /**
     * @var string
     */
    protected $area;

    /**
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
     * @return string
     */
    public function getArea() : string {
        return $this->area;
    }

    /**
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
     * @param $event
     * @return mixed
     */
    public function resolve($event) {
        return call_user_func($this->resolver, $event);
    }

}