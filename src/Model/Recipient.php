<?php

namespace Antares\Notifications\Model;

use Illuminate\Contracts\Support\Arrayable;
use Closure;

class Recipient implements Arrayable {

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Closure
     */
    protected $resolver;

    /**
     * Recipient constructor.
     * @param string $id
     * @param string $label
     * @param Closure $resolver
     */
    public function __construct(string $id, string $label, Closure $resolver) {
        $this->id = $id;
        $this->label = $label;
        $this->resolver = $resolver;
    }

    /**
     * @return string
     */
    public function getId() : string {
        return $this->id;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray() : array {
        return [
            'id'    => $this->id,
            'label' => $this->label,
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