<?php

namespace Antares\Notifications\Services;

use Antares\Modules\BillevioBase\Models\NotificationModuleVariables;
use Antares\Notifications\Contracts\ModelVariablesResoluble;
use Antares\Notifications\Parsers\VariableParser;
use Antares\Notifications\Variable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionParameter;

class VariablesService {

    /**
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * @var ModuleVariables[]
     */
    protected static $modules = [];

    /**
     * VariablesService constructor.
     * @param Dispatcher $dispatcher
     */
    public function __construct(Dispatcher $dispatcher) {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Registers new variables container for the given module name.
     *
     * @param string $moduleName
     * @param ModelVariablesResoluble|null $model
     * @return ModuleVariables
     */
    public function register(string $moduleName, ModelVariablesResoluble $model = null) : ModuleVariables {
        $module = $this->findByModule($moduleName);

        if($module === null) {
            $module = new ModuleVariables($moduleName);
        }

        if($model) {
            $model->applyVariables($module);
        }

        return self::$modules[$moduleName] = $module;
    }

    /**
     * Returns associative array which included full variable coded as key and Variable object as it value.
     *
     * @return Variable[]|array
     */
    public function getNamedVariables() : array {
        $data = [];

        foreach(self::$modules as $moduleVariables) {
            $data[] = $moduleVariables->getNamedVariables();
        }

        return array_merge(...$data);
    }

    /**
     * @return ModuleVariables[]|array
     */
    public function all() : array {
        return self::$modules;
    }

    /**
     * Returns variables container for the given module name. Returns null if not found.
     *
     * @param string $moduleName
     * @return ModuleVariables|null
     */
    public function findByModule(string $moduleName) : ?ModuleVariables {
        return Arr::get(self::$modules, $moduleName);
    }

    /**
     * Returns the variable object based on full variable code, ex. Shopping::order.id
     *
     * @param string $code
     * @return Variable|null
     * @throws \InvalidArgumentException
     */
    public function findByCode(string $code) : ?Variable {
        if( ! Str::contains($code, '::') ) {
            throw new \InvalidArgumentException('The given variable code is invalid. Must contains :: chars in the name but the [' . $code . '] has been give.');
        }

        list($module, $variable) = explode('::', $code);

        $moduleVariables = Arr::get(self::$modules, $module);

        if($moduleVariables instanceof ModuleVariables) {
            return $moduleVariables->get($variable);
        }

        return null;
    }

    /**
     * @param ReflectionParameter $parameter
     * @return mixed|null
     */
    public function getDefault(ReflectionParameter $parameter) {
        foreach(static::$modules as $module) {
            foreach($module->getModelDefinitions() as $definition) {
                if($definition->getBindParameter()->isMatchToParameter($parameter)) {
                    return $definition->getDefault();
                }
            }
        }

        return null;
    }

}