<?php

namespace Antares\Notifications\Contracts;

use Antares\Notifications\Services\ModuleVariables;

interface ModelVariablesResoluble {

    /**
     * Applies the variables to the module container.
     *
     * @param ModuleVariables $moduleVariables
     */
    public function applyVariables(ModuleVariables $moduleVariables) : void;

}
