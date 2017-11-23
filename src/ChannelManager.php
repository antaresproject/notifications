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

namespace Antares\Notifications;

use Antares\Notifications\Channels\TemplateChannel;
use Antares\Notifications\Services\TemplateBuilderService;
use Illuminate\Notifications\ChannelManager as BaseChannelManager;
use Antares\Notifications\Channels\NotificationChannel;
use Antares\Notifications\Channels\MailChannel;
use Antares\Notifications\Channels\SmsChannel;
use Antares\Notifier\Adapter\FastSmsAdapter;

class ChannelManager extends BaseChannelManager
{

    /**
     * Create an instance of the Notify Event driver.
     *
     * @return \Illuminate\Notifications\Channels\MailChannel
     */
    protected function createTemplateDriver()
    {
        return $this->app->make(TemplateChannel::class);
    }

    /**
     * Create an instance of the mail driver.
     *
     * @return \Illuminate\Notifications\Channels\MailChannel
     */
    protected function createMailDriver()
    {
        return $this->app->make(MailChannel::class);
    }

    /**
     * Create an instance of the sms driver.
     *
     * @return SmsChannel
     */
    protected function createSmsDriver()
    {
        $config     = config('services.fastSms', []);
        $adapter    = new FastSmsAdapter($config);
        $templateBuilderService = $this->app->make(TemplateBuilderService::class);

        return new SmsChannel($adapter, $templateBuilderService);
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
