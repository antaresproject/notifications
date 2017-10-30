<?php

namespace Antares\Notifications\Http\Form;

use Antares\Form\Controls\CustomType;
use Antares\Form\Controls\SwitchType;
use Antares\Form\Controls\TextType;
use Antares\Modules\BillevioBase\Http\Forms\VueFormBuilder;
use Antares\Contracts\Html\Builder;
use Antares\Html\Form\Fieldset;
use Antares\Html\Form\Grid as FormGrid;
use Antares\Notifications\Decorator\NotificationFormDecorator;
use Antares\Notifications\Model\NotificationCategory;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Model\NotificationSeverity;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Notifications\Services\EventsRegistrarService;
use Antares\Notifications\Services\VariablesService;

class NotificationForm {

    /**
     * @var EventsRegistrarService
     */
    protected $eventsRegistrarService;

    /**
     * @var VariablesService
     */
    protected $variablesService;

    /**
     * NotificationForm constructor.
     * @param EventsRegistrarService $eventsRegistrarService
     * @param VariablesService $variablesService
     */
    public function __construct(EventsRegistrarService $eventsRegistrarService, VariablesService $variablesService) {
        $this->eventsRegistrarService = $eventsRegistrarService;
        $this->variablesService = $variablesService;
    }

    /**
     * @param Notifications $notification
     * @return Builder
     */
    public function build(Notifications $notification) : Builder {
        $url    = $notification->exists
            ? handles('antares::notifications/' . $notification->id . '/update')
            : handles('antares::notifications/store');

        $form       = new VueFormBuilder($url);
        $langs      = langs();
        $contents   = [];

        foreach($langs as $lang) {
            $contents[$lang->code] = $notification->lang($lang);
        }

        $form->setDataProviders([
            'categories'    => NotificationCategory::all()->toJson(),
            'types'         => NotificationTypes::all()->toJson(),
            'severities'    => NotificationSeverity::all()->toJson(),
            'events'        => $this->eventsRegistrarService->getModels()->toJson(),
            'notification'  => $notification->toJson(),
            'contents'      => json_encode($contents),
            'variables'     => json_encode($this->getPreparedVariables()),
            'selected-lang' => $langs[0]->code,
        ]);

        return $form->build('notification', function(FormGrid $form) use($notification, $langs, $contents) {
            publish('notifications', ['js/vue-ckeditor.js']);
            publish('notifications', ['js/notification-form.js']);

            app('antares.asset')->container('antares/foundation::application')
                ->add('ckeditor', 'https://cdn.ckeditor.com/4.6.2/full-all/ckeditor.js', ['app_cache']);

            $controlsDecorator = new NotificationFormDecorator();

            $form->fieldset(function (Fieldset $fieldset) use($notification, $controlsDecorator) {

                $nameControl = (new TextType('name'))
                    ->withError()
                    ->setDecorator($controlsDecorator)
                    ->setLabel( trans('antares/notifications::messages.notification_name') )
                    ->setValue($notification->name)
                    ->setAttribute('v-model', 'notification.name');

                $activeControl = (new SwitchType('active'))
                    ->withError()
                    ->setDecorator($controlsDecorator)
                    ->setValue($notification->active)
                    ->setLabel( trans('antares/notifications::messages.notification_content_enabled') )
                    ->setAttribute('v-model', 'notification.active');

                $categoryControl = $this->makeDropdownControl('category_id', 'categories', 'notification.category', 'title')
                    ->withError()
                    ->setDecorator($controlsDecorator)
                    ->setValue($notification->category_id)
                    ->setLabel( trans('antares/notifications::messages.notification_category') );

                $typeControl = $this->makeDropdownControl('type_id', 'types', 'notification.type', 'title')
                    ->withError()
                    ->setDecorator($controlsDecorator)
                    ->setValue($notification->type_id)
                    ->setLabel( trans('antares/notifications::messages.notification_type') );

                $severityControl = $this->makeDropdownControl('severity_id', 'severities', 'notification.severity', 'title')
                    ->withError()
                    ->setDecorator($controlsDecorator)
                    ->setValue($notification->severity_id)
                    ->setLabel( trans('antares/notifications::messages.notification_severity') );

                $eventControl = (new CustomType('event_id', function() {
                        return '<v-select label="label" :on-change="eventChanged" :options="events" v-model="notification.event" ></v-select>';
                    }))
                    ->withError()
                    ->setDecorator($controlsDecorator)
                    ->setValue($notification->severity_id)
                    ->setLabel( trans('antares/notifications::messages.notification_event') );

                $recipientsControl = (new CustomType('recipients', function() {
                        return '<v-select multiple label="label" :options.sync="eventRecipients" v-model="notification.recipients"></v-select>';
                    }))
                    ->withError()
                    ->setDecorator($controlsDecorator)
                    ->setLabel( trans('antares/notifications::messages.notification_recipients') );

                $fieldset->addType($nameControl);
                $fieldset->addType($activeControl);
                $fieldset->addType($categoryControl);
                $fieldset->addType($typeControl);
                $fieldset->addType($severityControl);
                $fieldset->addType($eventControl);
                $fieldset->addType($recipientsControl);
            });

            /* @var $lang \Antares\Translations\Models\Languages */

            foreach($langs as $lang) {
                $form->fieldset(function(Fieldset $fieldset) use($notification, $lang, $contents, $controlsDecorator) {
                    $fieldset->legend(trans('antares/notifications::messages.notification_content_legend', ['lang' => $lang->name]));

                    $code       = $lang->code;
                    $content    = $contents[$code];

                    $titleControl = (new TextType('contents[' . $code . '][title]'))
                        ->withError()
                        ->setDecorator($controlsDecorator)
                        ->setLabel( trans('antares/notifications::messages.notification_content_title') )
                        ->setValue($content->title)
                        ->setAttributes([
                            'id' => 'notification-' . $code . '-title',
                            'data-lang-code' => $code,
                            'v-bind:disabled' => 'disabledContentTitle',
                            'v-model' => 'contents.' . $code . '.title',
                        ]);

                    $id = 'notification-' . $code . '-content';

                    $contentControl = (new CustomType('contents[' . $code . '][content]', function() use($id, $code) {
                            return '<vue-ckeditor id="' . $id . '" :value.sync="contents.' . $code . '.content"  v-model="contents.' . $code . '.content" :config="contentConfig"></vue-ckeditor>';
                        }))
                        ->withError()
                        ->setDecorator($controlsDecorator)
                        ->setLabel( trans('antares/notifications::messages.notification_content_content') )
                        ->setValue($content->content)
                        ->setAttributes([
                            'data-lang-code' => $code,
                        ]);

                    $fieldset->addType($titleControl);
                    $fieldset->addType($contentControl);
                });
            }

            $form->fieldset('Actions', function (Fieldset $fieldset) use($notification) {
                $fieldset->control('button', 'cancel')
                    ->field(function() {
                        return app('html')->link(handles("antares::notifications/"), trans('Cancel'), ['class' => 'btn btn--md btn--default mdl-button mdl-js-button']);
                    });

                $acl = app('antares.acl')->make('antares/notifications');

                if ($acl->can('notifications-preview')) {
                    $fieldset->control('button', 'preview')
                        ->attributes([
                            'id'         => 'notification-preview-button',
                            'type'       => 'button',
                            'value'      => trans('Preview'),
                            'class'      => 'btn btn-default notification-template-preview',
                            'data-url'   => handles('antares::notifications/preview'),
                            'data-title' => trans('antares/notifications::messages.generating_notification_preview')
                        ])
                        ->value(trans('Preview'));
                }

                if ($acl->can('notifications-test')) {
                    $fieldset->control('button', 'sendtest')
                        ->attributes([
                            'id'         => 'notification-send-test-button',
                            'type'       => 'button',
                            'class'      => 'btn btn-default send-test-notification',
                            'data-url'   => handles('antares::notifications/sendTest', ['csrf' => true])
                        ])
                        ->value(trans('Send test'));
                }

                $fieldset->control('button', 'button')
                    ->attributes(['type' => 'submit', 'class' => 'btn btn-primary'])
                    ->value($notification->exists ? trans('antares/foundation::label.save_changes') : trans('Save'));
            });
        });
    }

    /**
     * @return array
     */
    protected function getPreparedVariables() : array {
        $data = [];

        $data[] = [
            'module' => 'Instructions',
            'list' => [
                 [
                    'label' => 'foreach',
                    'code' => "[[foreach]]\n\t{% for element in [[list]] %}\n\t\t {{ element.attribute }}\n\t{% endfor %}\n[[/foreach]]"
                ],
                [
                    'label' => 'if',
                    'code' => "[[if]]\n\t{% if [[element.attribute]] == 'foo' %} \n\t\tfoo attribute\n\t{% endif %}\n[[/if]]"
                ],
            ],
        ];

        foreach($this->variablesService->all() as $moduleVariable) {
            $data[] = $moduleVariable->toArray();
        }

        return $data;
    }

    /**
     * @param string $name
     * @param string $options
     * @param string $value
     * @param string $label
     * @return CustomType
     */
    protected function makeDropdownControl(string $name, string $options, string $value, string $label = 'name') {
        $control = new CustomType($name, function() use($options, $value, $label) {
            return '<v-select label="' . $label . '" :options="' . $options . '" v-model="' . $value . '"></v-select>';
        });

        return $control;
    }

}
