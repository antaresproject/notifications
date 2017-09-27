<?php

namespace Antares\Notifications\Services;

use Antares\Notifications\BindParameter;
use Antares\Notifications\ModelVariableDefinitions;
use Antares\Notifications\Variable;
use Closure;

class ModuleVariables {

    /**
     * @var string
     */
    protected $module;

    /**
     * @var Variable[]
     */
    protected $variables = [];

    /**
     * @var ModelVariableDefinitions[]
     */
    protected $definitions = [];

    /**
     * ModuleVariables constructor.
     * @param string $module
     */
    public function __construct(string $module) {
        $this->module = $module;
    }

    /**
     * @param string $name
     * @param string $className
     * @param Closure $default
     * @return ModelVariableDefinitions
     */
    public function modelDefinition(string $name, string $className, Closure $default) : ModelVariableDefinitions {
        $definition = new ModelVariableDefinitions(new BindParameter($name, $className), $default);

        $this->definitions[$name] = $definition;

        return $definition;
    }

    /**
     * @param string $code
     * @param string $label
     * @param $value
     * @return ModuleVariables
     */
    public function set(string $code, string $label, $value) : self {
        $this->variables[$code] = new Variable($code, $label, $value);

        return $this;
    }

    /**
     * @param string $code
     * @return Variable|null
     */
    public function get(string $code) : ?Variable {
        foreach($this->all() as $variable) {
            if($variable->getCode() === $code) {
                return $variable;
            }
        }

        return null;
    }

    /**
     * @return ModelVariableDefinitions[]
     */
    public function getModelDefinitions() : array {
        return $this->definitions;
    }

    /**
     * @return Variable[]
     */
    public function all() : array {
        $variables = [];

        foreach($this->definitions as $definition) {
            foreach($definition->toVariables() as $variable) {
                $variables[] = $variable;
            }
        }

        return array_merge(array_values($this->variables), $variables);
    }

    /**
     * @return array
     */
    public function getNamedVariables() : array {
        $data = [];

        foreach($this->all() as $variable) {
            $placeholder = $this->module . '::' . $variable->getCode();

            $data[$placeholder] = $variable;
        }

        return $data;
    }

}