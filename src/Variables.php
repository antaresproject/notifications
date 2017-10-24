<?php

namespace Antares\Notifications;

use Antares\Notifications\Services\VariablesService;

class Variables
{

    /**
     * @var VariablesService
     */
    protected $variablesService;

    /**
     * Variables constructor.
     * @param VariablesService $variablesService
     */
    public function __construct(VariablesService $variablesService) {
        $this->variablesService = $variablesService;
    }

    /**
     * Gets available variable instructions
     * 
     * @return array[]
     */
    public function instructions() : array
    {
        return [
            'foreach' => [
                'description' => trans('antares/notifications::messages.instructions.description.foreach'),
                'instruction' => "[[foreach]]\n\t{% for element in [[list]] %}\n\t\t {{ element.attribute }}\n\t{% endfor %}\n[[/foreach]]"
            ],
            'if'      => [
                'description' => trans('antares/notifications::messages.instructions.description.if'),
                'instruction' => "[[if]]\n\t{% if [[element.attribute]] == 'foo' %} \n\t\tfoo attribute\n\t{% endif %}\n[[/if]]"
            ],
        ];
    }

    /**
     * Gets available variables
     * 
     * @return array
     */
    public function variables() : array
    {
        $variables = [];

        foreach($this->variablesService->all() as $name => $module) {
            $variables[$name] = array_map(function(Variable $variable) {
                return [
                    'name'          => $variable->getCode(),
                    'description'   => $variable->getLabel(),
                ];
            }, $module->all());
        }

        return $variables;
    }

}
