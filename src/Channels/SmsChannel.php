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

use Antares\Notifications\Exceptions\InfoException;
use Antares\Notifications\Messages\SmsMessage;
use Antares\Notifications\Services\TemplateBuilderService;
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
     * Template builder service instance.
     *
     * @var TemplateBuilderService
     */
    protected $templateBuilderService;

    /**
     * SmsChannel constructor.
     * @param FastSmsAdapter $adapter
     * @param TemplateBuilderService $templateBuilderService
     */
    public function __construct(FastSmsAdapter $adapter, TemplateBuilderService $templateBuilderService)
    {
        $this->adapter = $adapter;
        $this->templateBuilderService = $templateBuilderService;
    }

    /**
     * @param $notifiable
     * @param Notification $notification
     * @return bool
     * @throws Exception
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$to = $notifiable->routeNotificationFor('sms')) {
            return false;
        }
        $message = $notification->toSms($notifiable);

        if (is_string($message)) {
            $message = new SmsMessage($message);
        }

        $this->templateBuilderService->setNotification($notification)->build($message);

        try {
            $this->adapter->send($message, $to);
        } catch (Exception $e) {
            Log::error($e);

            throw new InfoException($e->getMessage());
        }
    }

}
