<?php

namespace Antares\Notifications;

use Illuminate\Notifications\ChannelManager as BaseChannelManager;
use Antares\Notifications\Channels\NotificationChannel;
use Antares\Notifications\Channels\MailChannel;
use Antares\Notifications\Channels\SmsChannel;
use Antares\Notifier\Adapter\FastSmsAdapter;
use Illuminate\Mail\Markdown;

class ChannelManager extends BaseChannelManager
{

    /**
     * Create an instance of the mail driver.
     *
     * @return \Illuminate\Notifications\Channels\MailChannel
     */
    protected function createMailDriver()
    {
        return $this->app->make(MailChannel::class)->setMarkdownResolver(function () {
            return $this->app->make(Markdown::class);
        });
    }

    /**
     * Create an instance of the sms driver.
     *
     * @return SmsChannel
     */
    protected function createSmsDriver()
    {
        $adapter = new FastSmsAdapter(config('antares/notifier::sms.adapters.fastSms'));
        return new SmsChannel($adapter);
    }

    /**
     * Create an instance of the alerts driver.
     *
     * @return NotificationChannel
     */
    protected function createAlertDriver()
    {
        return $this->app->make(NotificationChannel::class);
    }

    /**
     * Create an instance of the notifications driver.
     *
     * @return NotificationChannel
     */
    protected function createNotificationDriver()
    {
        return $this->app->make(NotificationChannel::class);
    }

}
