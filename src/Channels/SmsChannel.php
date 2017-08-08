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

namespace Antares\Notifications\Channels;

use Antares\Notifications\Messages\SmsMessage;
use Antares\Notifier\Adapter\FastSmsAdapter;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Exception;

class SmsChannel
{

    /**
     * FastSms client instance.
     *
     * @var FastSmsAdapter
     */
    protected $adapter;

    /**
     * Create a new FastSmsAdapter channel instance.
     *
     * @param  FastSmsAdapter  $adapter
     * @return void
     */
    public function __construct(FastSmsAdapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return \Nexmo\Message\Message
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$to = $notifiable->routeNotificationFor('sms')) {
            return;
        }
        $message = $notification->toSms($notifiable);

        if (is_string($message)) {
            $message = new SmsMessage($message);
        }
        try {
            $result = $this->adapter->send($message, $to);
            return $result;
        } catch (Exception $ex) {
            Log::error($ex);
            return;
        }
    }

}
