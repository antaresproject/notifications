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
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Http\Form;

use Antares\Notifications\Model\NotificationCategory;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Html\Form\ClientScript;
use Antares\Html\Form\FormBuilder;
use Antares\Html\Form\Fieldset;
use Antares\Html\Form\Grid;

class Form extends FormBuilder
{

    /**
     * available form rules 
     *
     * @var array
     */
    protected $rules = [
        'title'   => ['required', 'max:500'],
        'content' => ['required'],
    ];

    /**
     * Layout attributes
     *
     * @var array 
     */
    protected $layoutAttributes = [];

    /**
     * Notification configuration
     *
     * @var Fluent 
     */
    protected $fluent = null;

    /**
     * cosntructing
     * 
     * @param \Antares\View\Notification\Notification $notification
     * @param \Antares\Support\Fluent $fluent
     */
    public function __construct($notification, $fluent)
    {
        $this->fluent = $fluent;
        $clientScript = app(ClientScript::class);
        $grid         = app(Grid::class);

        parent::__construct($grid, $clientScript, app());

        $this->name             = "antares.notification: " . $fluent->form_name;
        $this->grid->simple(handles('antares::notifications/update/'), ['data-is-sms' => $fluent->type === 'sms'], $fluent);
        $this->layoutAttributes = [
            'variables'    => $notification->getVariables(),
            'instructions' => $notification->getInstructions(),
            'rich'         => true,
            'sms'          => $fluent->type === 'sms',
            'langs'        => langs(),
            'id'           => $fluent->id,
            'type'         => $fluent->type
        ];
        $this->bindScripts();
        $this->grid->layout('antares/notifications::admin.index.form', $this->layoutAttributes);
        $this->grid->hidden('id');
        $this->grid->name('Notification form');

        $this->grid->fieldset(function (Fieldset $fieldset) use($fluent, $notification) {

            $fieldset->legend('Notification parameters');

            $attrs   = ['class' => 'notification-select-type', 'url' => handles('antares::notifications/create')];
            $control = $fieldset->control('select', 'type')
                    ->label(trans('Type'))
                    ->wrapper(['class' => 'w180'])
                    ->options(function() {
                return NotificationTypes::all()->pluck('title', 'name');
            });

            if (!is_null($this->fluent->id)) {
                array_set($attrs, 'disabled', 'disabled');
            }
            $control->attributes($attrs);



            $fieldset->control('select', 'category')
                    ->label('Category')
                    ->options(function() {
                        return app(NotificationCategory::class)->get()->pluck('title', 'id');
                    })
                    ->value($this->fluent->type);


            $fieldset->control('input:checkbox', 'active')
                    ->field(function() use($fluent) {
                        $checked = $fluent->active ? 'checked="checked"' : '';
                        return '<input class="switch-checkbox" ' . $checked . ' name="active" type="checkbox" value="1" >';
                    });

            $this->buttons($fluent, $fieldset);
        });


        $langs = langs();
        foreach ($langs as $index => $lang) {

            $this->grid->fieldset(function (Fieldset $fieldset) use($fluent, $notification, $lang, $index) {
                $fieldset->legend($lang->name);
                $fieldset->control('input:text', 'title')
                        ->label(trans('antares/notifications::messages.notification_content_title'))
                        ->name('title[' . $lang->id . ']')
                        ->attributes(['rel' => $lang->code, 'class' => 'notification-title ' . (($index > 0) ? 'hidden' : '')])
                        ->value($this->getNotificationContentData($fluent, $lang->id));


                $fieldset->control('textarea', 'content')
                        ->label(trans('antares/notifications::messages.notification_content_content'))
                        ->attributes(['rel' => $lang->code, 'class' => 'richtext has-ckeditor hidden', 'rows' => 10, 'cols' => 80])
                        ->name('content[' . $lang->id . ']')
                        ->value($this->getNotificationContentData($fluent, $lang->id, 'content'));
            });
        }

        $this->grid->ajaxable();
        if (!in_array($fluent->type, ['sms', 'email'])) {
            unset($this->rules['title']);
        }
        $this->grid->rules($this->rules);
    }

    /**
     * buttons in form
     * 
     * @param Fluent $fluent
     * @param Fieldset $fieldset
     */
    protected function buttons($fluent, Fieldset $fieldset)
    {
        $fieldset->control('button', 'cancel')
                ->field(function() {
                    return app('html')->link(handles("antares::notifications/"), trans('Cancel'), ['class' => 'btn btn--md btn--default mdl-button mdl-js-button']);
                });

        $fieldset->control('button', 'button')
                ->attributes(['type' => 'submit', 'class' => 'btn btn-primary'])
                ->value($fluent->id ? trans('antares/foundation::label.save_changes') : trans('Save'));
    }

    /**
     * Bind scripts
     * 
     * @return \Antares\Asset\Asset
     */
    protected function bindScripts()
    {
        //$scripts = ($this->fluent->type == 'sms') ? ['js/ckeditor-notifications-sms.js'] : ['js/ckeditor-notifications.js'];
        $scripts = ['js/ckeditor-notifications.js'];
        return publish('notifications', $scripts);
    }

    /**
     * on create scenario
     * 
     * @param String $type
     * @return \Antares\Notifications\Http\Form\Form
     */
    public function onCreate()
    {
        $this->grid->attributes([
            'url'    => handles('antares::notifications/store'),
            'method' => 'POST',
        ]);
        $layout = ($this->fluent->type == 'sms') ? 'antares/notifications::admin.index.form_sms' : 'antares/notifications::admin.index.form';
        $this->grid->layout($layout, $this->layoutAttributes);
        return $this;
    }

    /**
     * Gets notification data
     * 
     * @param Fluent $fluent
     * @param mixed $langId
     * @param String $key
     * @return String
     */
    protected function getNotificationContentData($fluent, $langId, $key = 'title')
    {
        foreach ($fluent->contents as $content) {
            if ($langId !== $content['lang_id']) {
                continue;
            }
            return array_get($content, $key);
        }
        return '';
    }

}
