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

namespace Antares\Notifications\Services;

use Antares\Notifications\BindParameter;
use Antares\Notifications\ModelVariableDefinitions;
use Antares\Notifications\Variable;
use Closure;
use Illuminate\Contracts\Support\Arrayable;

class ModuleVariables implements Arrayable
{

    /**
     * Module name.
     *
     * @var string
     */
    protected $module;

    /**
     * List of variables.
     *
     * @var Variable[]
     */
    protected $variables = [];

    /**
     * List of model definitions.
     *
     * @var ModelVariableDefinitions[]
     */
    protected $definitions = [];

    /**
     * ModuleVariables constructor.
     * @param string $module
     */
    public function __construct(string $module)
    {
        $this->module = $module;
    }

    /**
     * Defines model definition for variables.
     *
     * @param string $name
     * @param string $className
     * @param Closure $default
     * @return ModelVariableDefinitions
     */
    public function modelDefinition(string $name, string $className, Closure $default): ModelVariableDefinitions
    {
        $definition = new ModelVariableDefinitions(new BindParameter($name, $className), $default);

        $this->definitions[$name] = $definition;

        return $definition;
    }

    /**
     * Returns module name.
     *
     * @return string
     */
    public function getModuleName(): string
    {
        return $this->module;
    }

    /**
     * Sets single variable.
     *
     * @param string $code
     * @param string $label
     * @param $value
     * @return ModuleVariables
     */
    public function set(string $code, string $label, $value): self
    {
        $this->variables[$code] = new Variable($code, $label, $value);

        return $this;
    }

    /**
     * Returns variable by the given code if exists.
     *
     * @param string $code
     * @return Variable|null
     */
    public function get(string $code)
    {
        foreach ($this->all() as $variable) {
            if ($variable->getCode() === $code) {
                return $variable;
            }
        }

        return null;
    }

    /**
     * Returns list of model definitions.
     *
     * @return ModelVariableDefinitions[]
     */
    public function getModelDefinitions(): array
    {
        return $this->definitions;
    }

    /**
     * Returns all variables.
     *
     * @return Variable[]
     */
    public function all(): array
    {
        $variables = [];

        foreach ($this->definitions as $definition) {
            foreach ($definition->toVariables() as $variable) {
                $variables[] = $variable;
            }
        }

        return array_merge(array_values($this->variables), $variables);
    }

    /**
     * Returns all variables in format module-name::code
     *
     * @return array
     */
    public function getNamedVariables(): array
    {
        $data = [];

        foreach ($this->all() as $variable) {
            $placeholder = $this->module . '::' . $variable->getCode();

            $data[$placeholder] = $variable;
        }

        return $data;
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $list = [];

        foreach ($this->all() as $variable) {
            $list[] = [
                'label' => $variable->getLabel(),
                'code'  => '[[ ' . $this->module . '::' . $variable->getCode() . ' ]]',
            ];
        }

        return [
            'module' => $this->getModuleName(),
            'list'   => $list,
        ];
    }

}
