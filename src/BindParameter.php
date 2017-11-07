<?php

namespace Antares\Notifications;

use Illuminate\Support\Arr;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionParameter;

class BindParameter {

    /**
     * Variable name.
     *
     * @var string
     */
    protected $variableName;

    /**
     * CLass name.
     *
     * @var string
     */
    protected $className;

    /**
     * BindParameter constructor.
     * @param string $variableName
     * @param string $className
     */
    public function __construct(string $variableName, string $className) {
        $this->variableName = $variableName;
        $this->className    = $className;

        if( ! class_exists($className) ) {
            throw new InvalidArgumentException('The given class name does not exists.');
        }
    }

    /**
     * Returns variable name.
     *
     * @return string
     */
    public function getVariableName() : string {
        return $this->variableName;
    }

    /**
     * Returns class name.
     *
     * @return string
     */
    public function getClassName() : string {
        return $this->className;
    }

    /**
     * Checks if the given value is matched to variable definition.
     *
     * @param $value
     * @return bool
     */
    public function isMatchToValue($value) : bool {
        return (is_object($value) && (new ReflectionClass($value))->getName() === $this->getClassName());
    }

    /**
     * Checks if the variable is present as a key in the given data array.
     *
     * @param array $data
     * @return bool
     */
    public function isMatchIn(array $data) : bool {
        $value = Arr::get($data, $this->variableName);

        return !! $value;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return bool
     */
    public function isMatchToParameter(ReflectionParameter $parameter) : bool {
        return $this->getClassName() === $parameter->getType()->getName() && $this->getVariableName() === $parameter->getName();
    }

}