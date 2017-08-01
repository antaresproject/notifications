<?php

namespace Antares\Notifications;

use Illuminate\Notifications\ChannelManager as BaseChannelManager;
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
        return $this->app->make(Channels\MailChannel::class)->setMarkdownResolver(function () {
                    return $this->app->make(Markdown::class);
                });
    }

    /**
     * Create an instance of the Nexmo driver.
     *
     * @return \Illuminate\Notifications\Channels\NexmoSmsChannel
     */
    protected function createSmsDriver()
    {
        return new Channels\SmsChannel();
    }

}
