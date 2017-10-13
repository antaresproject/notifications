<?php

namespace Antares\Notifications;

use Antares\Support\Str;
use DomainException;
use Closure;

class ModelVariableDefinitions {

    /**
     * Bind parameter instance.
     *
     * @var BindParameter
     */
    protected $bindParameter;

    /**
     * An array of attributes.
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Default value defined as closure.
     *
     * @var Closure
     */
    protected $default;

    /**
     * ModelVariableDefinitions constructor.
     * @param BindParameter $bindParameter
     * @param Closure $default
     */
    public function __construct(BindParameter $bindParameter, Closure $default) {
        $this->bindParameter    = $bindParameter;
        $this->default          = $default;
    }

    /**
     * Returns bind parameter object.
     *
     * @return BindParameter
     */
    public function getBindParameter() : BindParameter {
        return $this->bindParameter;
    }

    /**
     * Clear and sets attributes for which the variable should be appears in the editor.
     *
     * @param array $attributes
     * @return ModelVariableDefinitions
     */
    public function setAttributes(array $attributes) : self {
        $this->attributes = [];

        foreach($attributes as $attribute => $label) {
            $this->addAttribute($attribute, $label);
        }

        return $this;
    }

    /**
     * Sets attributes for which the variable should be appears in the editor.
     *
     * @param string $attribute
     * @param string $label
     * @return ModelVariableDefinitions
     */
    public function addAttribute(string $attribute, string $label) : self {
        $this->attributes[$attribute] = $label;

        return $this;
    }

    /**
     * Returns attributes.
     *
     * @return string[]
     */
    public function getAttributes() : array {
        return $this->attributes;
    }

    /**
     * Returns an array with Variable objects.
     *
     * @return Variable[]
     */
    public function toVariables() : array {
        $variables = [];

        foreach($this->attributes as $attribute => $label) {
            $name   = $this->bindParameter->getVariableName();
            $code   = $name . '.' . $attribute;
            $label  = Str::humanize( Str::singular($name) ) . ' ' . $label;

            $variable = new Variable($code, $label, function() use($code) {
                return $code;
            });

            $variable->setRequiredParameter($this->bindParameter);
            $variable->setAsCompiled(false);

            $variables[] = $variable;
        }

        return $variables;
    }

    /**
     * Checks if the model definition is satisfied in the given data array.
     *
     * @param array $data
     * @return bool
     */
    public function isSatisfiedRequirements(array $data = []) : bool {
        return $this->bindParameter->isMatchIn($data);
    }

    /**
     * Returns attributes as placeholders which contain model variable name at the beginning.
     *
     * Examples:
     * - order.number
     * - order.items
     * - invoice.customer
     *
     * @return string[]
     */
    public function getPlaceholders() : array {
        return array_map(function(string $attribute) {
            return $this->bindParameter->getVariableName() . '.' . $attribute;
        }, array_keys($this->attributes));
    }

    /**
     * Returns default object. Used in the editor preview mode to show model which is not passed by the external service.
     *
     * @return mixed
     * @throws DomainException
     */
    public function getDefault() {
        $value = value($this->default);

        if($this->bindParameter->isMatchToValue($value)) {
            return $value;
        }

        throw new DomainException('The default value is not match to the declared one.');
    }

}
