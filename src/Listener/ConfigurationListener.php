<?php

namespace Antares\Notifications\Listener;

use Antares\Memory\Model\Option;
use Antares\Html\Form\Fieldset;
use Illuminate\Support\Fluent;
use Antares\Html\Form\Grid;

class ConfigurationListener
{

    /**
     * Handles the security form event.
     *
     * @param SecurityFormSubmitted $securityFormSubmitted
     */
    public function handle(Fluent $model, Grid $grid)
    {
        $grid->fieldset(function(Fieldset $fieldset) {
            $fieldset->legend(trans('antares/notifications::logs.form.notifications_config_legend'));
            $memory = app('antares.memory')->make('primary');
            $fieldset->control('input:text', 'days')
                    ->label(trans('antares/notifications::logs.form.notifications_config_days_label'))
                    ->tip(trans('antares/notifications::logs.form.notifications_config_days_help'))
                    ->attributes(['class' => 'w100'])
                    ->value($memory->get('notifications_remove_after_days', ''));
        });
        $grid->rules(array_merge($grid->rules, [
            'days' => ['numeric'],
        ]));
    }

    /**
     * Save notifications configuration
     * 
     * @param Option $model
     */
    public function updated(Option $model)
    {

        $model        = Option::query()->firstOrNew([
            'name' => 'notifications_remove_after_days'
        ]);
        $model->value = input('days');
        return $model->save();
    }

}
