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

namespace Antares\Notifications\Listener;

use Antares\Notifications\Repository\Repository;
use Antares\Notifications\Event\EventDispatcher;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;

class NotificationsListener
{

    use DispatchesJobs;

    /**
     * Repository instance
     *
     * @var Repository 
     */
    protected $repository;

    /**
     * Event dispatcher instance.
     *
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * NotificationsListener constructor.
     * @param Repository $repository
     * @param EventDispatcher $eventDispatcher
     */
    public function __construct(Repository $repository, EventDispatcher $eventDispatcher)
    {
        $this->repository       = $repository;
        $this->eventDispatcher  = $eventDispatcher;
    }

    /**
     * Listening for notifications
     */
    public function listen()
    {
        $notifications = $this->repository->findSendable()->toArray();

        foreach ($notifications as $notification) {
            $this->listenNotificationsEvents($notification);
        }
    }

    /**
     * Runs notification events.
     *
     * @param array $notification
     */
    protected function listenNotificationsEvents(array $notification)
    {
        $events = Arr::get($notification, 'event');

        if($events === null && isset($notification['classname'])) {
            $events = app()->make($notification['classname'])->getEvents();
        }

        foreach ((array) $events as $event) {
            $this->runNotificationListener($event, $notification);
        }
    }

    /**
     * Runs notification listener.
     *
     * @param string $event
     * @param array $notification
     */
    protected function runNotificationListener(string $event, array $notification)
    {
        app('events')->listen($event, function(array $variables = null, array $recipients = null) use($notification) {
            $this->eventDispatcher->run($notification, $variables, $recipients);
        });
    }

}
