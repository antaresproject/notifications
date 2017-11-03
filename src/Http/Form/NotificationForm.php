<?php

namespace Antares\Notifications\Http\Form;

use Antares\Form\VueFormBuilder;
use Antares\Contracts\Html\Builder;
use Antares\Html\Form\Fieldset;
use Antares\Html\Form\Grid as FormGrid;
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
            'langs'         => json_encode($langs),
        ]);

        return $form->build('notification', function(FormGrid $form) use($notification, $langs, $contents) {
            publish('notifications', ['js/ckeditor-notifications.js']);
            publish('notifications', ['js/vue-ckeditor.js']);
            publish('notifications', ['js/vue-codemirror.js']);
            publish('notifications', ['js/notification-form.js']);

            app('antares.asset')->container('antares/foundation::application')->add('vue_min', '//cdnjs.cloudflare.com/ajax/libs/vue/2.4.4/vue.js', ['app_cache']);
            app('antares.asset')->container('antares/foundation::application')->add('lodash', '//cdnjs.cloudflare.com/ajax/libs/lodash.js/4.17.4/lodash.min.js', ['app_cache']);
            app('antares.asset')->container('antares/foundation::application')->add('clipboard', '//cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.7.1/clipboard.min.js', ['app_cache']);

            app('antares.asset')->container('antares/foundation::application')
                ->add('view_brand_settings', '/webpack/view_brand_settings.js', ['app-cache'])
                ->add('ckeditor', 'https://cdn.ckeditor.com/4.6.2/full-all/ckeditor.js', ['view_brand_settings']);

            $form->layout('antares/notifications::admin.index.form');

            $form->fieldset('Actions', function (Fieldset $fieldset) use($notification) {
                $fieldset->control('button', 'cancel')
                    ->field(function() {
                        return app('html')->link(handles("antares::notifications/"), trans('Cancel'), ['class' => 'btn btn--md btn--default mdl-button mdl-js-button']);
                    });

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

}
