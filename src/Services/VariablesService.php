<?php

namespace Antares\Notifications\Services;

use Antares\Notifications\Contracts\ModelVariablesResoluble;
use Antares\Notifications\ModelVariableDefinitions;
use Antares\Notifications\Variable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionParameter;

class VariablesService
{

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ModuleVariables[]
     */
    protected $modules = [];

    /**
     * VariablesService constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Registers new variables container for the given module name.
     *
     * @param string $moduleName
     * @param ModelVariablesResoluble|null $model
     * @return ModuleVariables
     */
    public function register(string $moduleName, ModelVariablesResoluble $model = null): ModuleVariables
    {
        $module = $this->findByModule($moduleName);

        if ($module === null) {
            $module = new ModuleVariables($moduleName);
        }

        if ($model) {
            $model->applyVariables($module);
        }

        return $this->modules[$moduleName] = $module;
    }

    /**
     * Returns associative array which included full variable coded as key and Variable object as it value.
     *
     * @return Variable[]|array
     */
    public function getNamedVariables(): array
    {
        $data = [];

        foreach ($this->modules as $moduleVariables) {
            $data[] = $moduleVariables->getNamedVariables();
        }

        return count($data) ? array_merge(...$data) : [];
    }

    /**
     * @return ModuleVariables[]|array
     */
    public function all(): array
    {
        return $this->modules;
    }

    /**
     * Returns variables container for the given module name. Returns null if not found.
     *
     * @param string $moduleName
     * @return ModuleVariables|null
     */
    public function findByModule(string $moduleName)
    {
        return Arr::get($this->modules, $moduleName);
    }

    /**
     * Returns the variable object based on full variable code, ex. shopping::order.id
     *
     * @param string $code
     * @return Variable|null
     * @throws \InvalidArgumentException
     */
    public function findByCode(string $code)
    {
        if (!Str::contains($code, '::')) {
            throw new \InvalidArgumentException('The given variable code is invalid. Must contains :: chars in the name but the [' . $code . '] has been give.');
        }

        list($module, $variable) = explode('::', $code);

        $moduleVariables = Arr::get($this->modules, $module);

        if ($moduleVariables instanceof ModuleVariables) {
            return $moduleVariables->get($variable);
        }

        return null;
    }

    /**
     * Returns the first matched module variables by the parameter.
     *
     * @param ReflectionParameter $parameter
     * @return ModuleVariables|null
     */
    public function firstModuleVariablesByParameter(ReflectionParameter $parameter) : ?ModuleVariables
    {
        foreach ($this->modules as $module) {
            foreach ($module->getModelDefinitions() as $definition) {
                if ($definition->getBindParameter()->isMatchToParameter($parameter)) {
                    return $module;
                }
            }
        }

        return null;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return mixed|null
     */
    public function getDefault(ReflectionParameter $parameter)
    {
        foreach ($this->modules as $module) {
            foreach ($module->getModelDefinitions() as $definition) {
                if ($definition->getBindParameter()->isMatchToParameter($parameter)) {
                    return $definition->getDefault();
                }
            }
        }

        return null;
    }

}
