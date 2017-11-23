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

namespace Antares\Notifications\Widgets\NotificationSender;

use Antares\Notifications\Widgets\NotificationSender\Controller\NotificationController;
use Antares\Notifications\Widgets\NotificationSender\Form\NotificationWidgetForm;
use Antares\UI\UIComponents\Adapter\AbstractTemplate;
use Illuminate\Support\Facades\Route;

class NotificationsWidget extends AbstractTemplate
{

    /**
     * name of widget
     * 
     * @var String 
     */
    public $name = 'Notifications Widget';

    /**
     * Widget title at top bar
     *
     * @var String 
     */
    protected $title = 'Send notification';

    /**
     * Form instance
     *
     * @var NotificationWidgetForm 
     */
    protected $form;

    /**
     * widget attributes
     *
     * @var array
     */
    protected $attributes = [
        'min_width'      => 3,
        'min_height'     => 4,
        'max_width'      => 52,
        'max_height'     => 52,
        'default_width'  => 5,
        'default_height' => 6,
        'titlable'       => true,
    ];

    /**
     * Where widget should be available 
     *
     * @var array
     */
    protected $views = [
        'antares/foundation::admin.users.show'
    ];

    /**
     * Construct
     * 
     * @param NotificationWidgetForm $form
     */
    public function __construct(NotificationWidgetForm $form)
    {
        parent::__construct();
        $this->form = $form;
    }

    /**
     * Widgets routes implementations
     * 
     * @return void
     */
    public static function routes()
    {
        $area = area();
        Route::post($area . '/notifications/notifications', NotificationController::class . '@index');
        Route::post($area . '/notifications/widgets/send', NotificationController::class . '@send');
    }

    /**
     * Renders widget content
     * 
     * @return \Illuminate\View\View
     */
    public function render()
    {
        app('antares.asset')->container('antares/foundation::application')->add('vue_min', '//cdnjs.cloudflare.com/ajax/libs/vue/2.4.4/vue.min.js', ['app_cache']);

        publish('notifications', ['js/notification-widget.js']);
        return view('antares/notifications::widgets.send_notification', ['form' => $this->form->get()])->render();
    }

}
