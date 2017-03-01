<?php

/**
 * Part of the Antares Project package.
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
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Http\Form;

use Antares\Html\Form\Fieldset;
use Illuminate\Support\Fluent;
use Antares\Html\Form\Grid;

class Configuration extends Grid
{

    /**
     * Construct
     * 
     * @param Model $model
     */
    public function __construct(Fluent $model)
    {
        parent::__construct(app());

        $this->name('Notifications Config');

        $this->simple(handles('antares::notifications/logs/config'), ['class' => 'col-dt-12'], $model);

        $this->fieldset(function (Fieldset $fieldset) {

            $fieldset->legend(trans('antares/notifications::logs.form.notifications_config_legend'));

            $fieldset->control('input:text', 'days')
                    ->label(trans('antares/notifications::logs.form.notifications_config_days_label'))
                    ->inlineHelp(trans('antares/notifications::logs.form.notifications_config_days_help'))
                    ->attributes(['class' => 'w100']);

            $this->buttons($fieldset);
        });


        $this->rules([
            'days' => ['numeric'],
        ]);
    }

    /**
     * Buttons in form
     * 
     * @param Fieldset $fieldset
     */
    protected function buttons(Fieldset $fieldset)
    {
        return $fieldset->control('button', 'button')
                        ->attributes(['type' => 'submit', 'class' => 'btn btn-primary'])
                        ->value(trans('antares/foundation::label.save_changes'));
    }

}
