<?php

namespace Antares\Notifications\Listener;

use Illuminate\Notifications\Events\NotificationSending as LaravelNotificationSending;
use Antares\Notifications\Synchronizer;
use Antares\Notifier\Mail\Mailer;

class NotificationSending
{

    /**
     * Mailer instance
     *
     * @var Mailer
     */
    protected $mailer;

    /**
     * Synchronizer instance
     *
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * Construct
     * 
     * @param Mailer $mailer
     * @param Synchronizer $synchronizer
     */
    public function __construct(Mailer $mailer, Synchronizer $synchronizer)
    {
        $this->mailer       = $mailer;
        $this->synchronizer = $synchronizer;
    }

    /**
     * Handle the event.
     *
     * @param  LaravelNotificationSending  $event
     * @return void
     */
    public function handle(LaravelNotificationSending $event)
    {

        if ($event->channel === 'mail') {

            $message = $event->notification->toMail($event->notifiable);
            $this->synchronizer->syncDatabase(get_class($event->notification), $message);
        }
    }

}
