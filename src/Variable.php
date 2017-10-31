<?php

namespace Antares\Notifications;

use Closure;
use InvalidArgumentException;

class Variable
{

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Closure|mixed
     */
    protected $value;

    /**
     * @var BindParameter|null
     */
    protected $bindParameter;

    /**
     * @var bool
     */
    protected $compiled = true;

    /**
     * Variable constructor.
     * @param string $code
     * @param string $label
     * @param Closure|mixed $value
     */
    public function __construct(string $code, string $label, $value)
    {
        $this->code  = $code;
        $this->label = $label;
        $this->value = $value;

        if (!($this->isSimpleType() || $value instanceof Closure)) {
            throw new InvalidArgumentException('Invalid value. Must be string, integer, float or Closure object.');
        }
    }

    /**
     * @param bool $state
     */
    public function setAsCompiled(bool $state): void
    {
        $this->compiled = $state;
    }

    /**
     * Sets bind parameter as required condition.
     *
     * @param BindParameter $bindParameter
     */
    public function setRequiredParameter(BindParameter $bindParameter): void
    {
        $this->bindParameter = $bindParameter;
    }

    /**
     * Returns bind parameter if assigned.
     *
     * @return BindParameter|null
     */
    public function getRequiredParameter()
    {
        return $this->bindParameter;
    }

    /**
     * Returns variable code.
     *
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * Returns variable label for editor.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Returns evaluated value.
     *
     * @return Closure|mixed
     */
    public function getValue()
    {
        return value($this->value);
    }

    /**
     * Determines if the given value is simple type (assumed as not Closure object).
     *
     * @return bool
     */
    public function isSimpleType(): bool
    {
        return is_string($this->value) || is_integer($this->value) || is_float($this->value);
    }

    /**
     * @return bool
     */
    public function isCompiled(): bool
    {
        return $this->compiled;
    }

}
