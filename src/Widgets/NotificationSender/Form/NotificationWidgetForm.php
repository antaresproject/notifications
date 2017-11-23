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

namespace Antares\Notifications\Widgets\NotificationSender\Form;

use Antares\Contracts\Html\Form\Grid as FormGrid;
use Antares\Notifications\Repository\Repository;
use Antares\Contracts\Html\Form\Fieldset;
use Antares\Asset\Factory;

class NotificationWidgetForm
{

    /**
     * Repository instance
     *
     * @var Repository 
     */
    protected $repository;

    /**
     * Assets factory instance
     *
     * @var Factory
     */
    protected $assets;

    /**
     * Construct
     * 
     * @param Repository $repository
     * @param Factory $assets
     */
    public function __construct(Repository $repository, Factory $assets)
    {
        $this->repository = $repository;
        $this->assets     = $assets;
    }

    /**
     * Definition of widget form validation rules
     * 
     * @var array 
     */
    protected $rules = [
        'notifications' => ['required']
    ];

    /**
     * Gets form instance
     * 
     * @return \Antares\Html\Form\FormBuilder
     */
    public function get()
    {
        $this->scripts();
        return app('antares.form')->of("antares.widgets: notification-widget")->extend(function (FormGrid $form) {

            $form->name('Notification Tester');
            $form->simple(handles('antares::notifications/widgets/send'), ['id' => 'notification-widget-form']);

            $form->layout('antares/notifications::widgets.forms.send_notification_form');

            $events = [
                'Antares\Logger\Events\NewDeviceDetected',
            ];

            $notifications = $this->repository->getNotificationContentsByEvents($events, 'mail')->pluck('notification.name', 'id');

            if($notifications->isEmpty()) {
                $form->layout('antares/notifications::widgets.forms.send_notification_zero_data');
            }
            else {
                $form->fieldset(trans('Default Fieldset'), function (Fieldset $fieldset) use($notifications) {

                    $fieldset->control('input:hidden', 'url')
                        ->attributes(['class' => 'notification-widget-url'])
                        ->value(handles('antares::notifications/notifications'))
                        ->block(['class' => 'hidden']);

                    $fieldset->control('select', 'type')
                        ->attributes(['class' => 'notification-widget-change-type-select', 'url' => handles('antares::notifications/notifications')])
                        ->options($this->repository->getDecoratedNotificationTypes());

                    $fieldset->control('select', 'notifications')
                        ->attributes(['class' => 'notification-widget-notifications-select'])
                        ->options($notifications);

                    if (!is_null(from_route('user'))) {
                        $fieldset->control('button', 'send')
                            ->attributes([
                                'type'       => 'submit',
                                'class'      => 'notification-widget-send-button',
                                'data-title' => trans('Are you sure to send notification?'),
                                'url'        => handles('antares::notifications/widgets/send'),
                            ])->value(trans('Send'));
                    }
                    $fieldset->control('button', 'test')
                        ->attributes([
                            'type'       => 'submit',
                            'class'      => 'notification-widget-test-button btn--red',
                            'data-title' => trans('Are you sure to test notification?'),
                            'url'        => handles('antares::notifications/widgets/test'),
                        ])->value(trans('Test'));
                });
            }


            $form->rules($this->rules);
            $form->ajaxable([
                'afterValidate' => $this->afterValidateInline()
            ]);
        });
    }

    /**
     * After validate form
     * 
     * @return String
     */
    protected function afterValidateInline()
    {
        return <<<EOD
js:function(form, data, hasError) { 
    if(hasError===false){     
        var form=$('#notification-widget-form');
        $('<input />').attr({type:'hidden','name':'afterValidate',value:true}).appendTo(form);
        $.post(form.attr('action'), form.serialize(), function (res) {
            noty($.extend({}, APP.noti.successFM("lg", "full"), {
                text: res.message                
            }));
            $('#notification-widget-form').find('input[name=afterValidate]').remove();
        });          
        return false;                    
    }}
EOD;
    }

    /**
     * Appends scripts container
     * 
     * @return \Antares\Asset\Asset
     */
    protected function scripts()
    {
        return $this->assets->container('antares/foundation::scripts')->add('notification-widget-js', 'packages/antares/notifications/js/notification-widget.js');
    }

}
