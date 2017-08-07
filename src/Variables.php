<?php

namespace Antares\Notifications;

class Variables
{

    /**
     * Gets available variable instructions
     * 
     * @return array
     */
    public function instructions()
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
    public function variables()
    {
        $variables  = app('antares.notifications')->all();
        $extensions = app('antares.memory')->make('component')->get('extensions.active');

        if (empty($variables)) {
            return [];
        }
        $return = [];
        foreach ($variables as $extension => $config) {
            $name = ucfirst($extension == 'foundation' ? $extension : array_get($extensions, $extension . '.name'));
            $vars = $config['variables'];
            if (empty($vars)) {
                continue;
            }
            foreach ($vars as $key => $variable) {
                $return[$name][] = ['name' => $key, 'description' => isset($variable['description']) ? $variable['description'] : ''];
            }
        }
        event('notifications:' . snake_case(class_basename($this)) . '.variables', [&$return]);

        return $return;
    }

}
