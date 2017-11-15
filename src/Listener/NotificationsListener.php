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

namespace Antares\Notifications\Listener;

use Antares\Notifications\Model\NotifiableEvent;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Services\NotificationsService;
use Illuminate\Contracts\Events\Dispatcher;

class NotificationsListener
{

    /**
     * Event Dispatcher instance.
     *
     * @var Dispatcher
     */
    protected $dispatcher;

    /**
     * Notifications service instance.
     *
     * @var NotificationsService
     */
    protected $notificationsService;

    /**
     * NotificationsListener constructor.
     * @param Dispatcher $dispatcher
     * @param NotificationsService $notificationsService
     */
    public function __construct(Dispatcher $dispatcher, NotificationsService $notificationsService) {
        $this->dispatcher = $dispatcher;
        $this->notificationsService = $notificationsService;
    }

    /**
     * Listening for notifications
     */
    public function boot()
    {
        /* @var $notifications Notifications[] */
        $notifications = Notifications::query()->where('active', true)->whereNotNull('event')->with('contents')->get();

        foreach ($notifications as $notification) {
            $this->listenNotificationEvent($notification);
        }
    }

    /**
     * Handles listening for given notification.
     *
     * @param Notifications $notification
     */
    protected function listenNotificationEvent(Notifications $notification)
    {
        if($notification->event_model instanceof NotifiableEvent) {
            $eventClass = $notification->event_model->getEventClass();

            if(class_exists($eventClass)) {
                $this->dispatcher->listen($eventClass, function($event) use($notification) {
                    $this->notificationsService->handle($notification, $event);
                });
            }
        }
    }

}
