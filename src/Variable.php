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

namespace Antares\Notifications;

use Closure;
use InvalidArgumentException;

class Variable
{

    /**
     * Variable code.
     *
     * @var string
     */
    protected $code;

    /**
     * Variable label.
     *
     * @var string
     */
    protected $label;

    /**
     * Variable value.
     *
     * @var Closure|mixed
     */
    protected $value;

    /**
     * Bind parameter instance.
     *
     * @var BindParameter|null
     */
    protected $bindParameter;

    /**
     * Determines if variable is compiled.
     *
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
     * Sets variable as compiled.
     *
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
     * Determines if variable is compiled.
     *
     * @return bool
     */
    public function isCompiled(): bool
    {
        return $this->compiled;
    }

}
