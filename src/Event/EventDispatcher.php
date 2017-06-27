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

namespace Antares\Notifications\Event;

use Antares\Foundation\Template\SendableNotification;
use Antares\Foundation\Template\CustomNotification;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Foundation\Template\EmailNotification;
use Antares\View\Contracts\NotificationContract;
use Antares\Foundation\Template\SmsNotification;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Arr;

class EventDispatcher
{

    use DispatchesJobs;

    /**
     * Sends notification.
     *
     * @param array $notification
     * @param array|null $variables
     * @param array|null $recipients
     * @return bool|\Symfony\Component\HttpFoundation\Response
     */
    public function run(array $notification, array $variables = null, array $recipients = null)
    {
        $typeName = Arr::get($notification, 'type.name');

        switch ($typeName) {
            case 'email':
                $instance = app(EmailNotification::class);
                break;
            case 'sms':
                $instance = app(SmsNotification::class);
                break;
            default:
                $instance = app(CustomNotification::class);
                break;
        }
        $instance->setPredefinedVariables($variables);
        $instance->setModel($notification);

        if ($instance instanceof SendableNotification) {
            $instance->setRecipients($recipients);

            if (!$this->validate($instance)) {
                return false;
            }
            $type = $instance->getType();
            $job  = $instance->onConnection('database')->onQueue($type);
            $this->dispatch($job);
        } else {
            return $instance->handle();
        }
    }

    /**
     * validates notification
     * 
     * @param NotificationContract $instance
     * @return boolean
     */
    protected function validate($instance)
    {
        return $instance instanceof NotificationContract;
    }

}
