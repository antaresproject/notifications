<?php

namespace Antares\Notifications\Listener;

use Illuminate\Notifications\Events\NotificationSending as LaravelNotificationSending;
use Antares\Notifications\Channels\MailChannel;
use Antares\Notifications\Channels\SmsChannel;
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
        if (in_array($event->channel, [MailChannel::class, 'mail'])) {
            $message = $event->notification->toMail($event->notifiable);
            $this->synchronizer->syncDatabase(get_class($event->notification), $message);
        } elseif (in_array($event->channel, [SmsChannel::class, 'sms'])) {
            $message = $event->notification->toSms($event->notifiable);
            $this->synchronizer->syncDatabase(get_class($event->notification), $message);
        } else {
            $channels = $event->notification->via($event->notifiable);
            foreach ($channels as $via) {
                $method  = 'to' . studly_case($via);
                $message = $event->notification->$method($event->notifiable);
                $this->synchronizer->syncDatabase(get_class($event->notification), $message);
            }
        }
    }

}
