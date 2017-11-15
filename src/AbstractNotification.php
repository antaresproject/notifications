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
use Antares\Notifications\Messages\MailMessage;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Messages\SmsMessage;
use Antares\Notifications\Model\Notifications;
use Illuminate\Notifications\Notification;

class AbstractNotification extends Notification
{

    /**
     * Is notification during test action.
     *
     * @var bool
     */
    public $testable = false;

    /**
     * Notification Template
     *
     * @var Notifications|null
     */
    public $template;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TemplateChannel::class];
    }

    /**
     * Returns mail message.
     *
     * @param $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable) {
        $data = $this->getResolvedData();

        return (new MailMessage)
            ->template('mail')
            ->subjectData($data)
            ->viewData($data);
    }

    /**
     * Returns SMS message.
     *
     * @param $notifiable
     * @return SmsMessage
     */
    public function toSms($notifiable) {
        $data = $this->getResolvedData();

        return (new SmsMessage())
            ->template('sms')
            ->viewData($data);
    }

    /**
     * Returns notification message.
     *
     * @param $notifiable
     * @return NotificationMessage
     */
    public function toNotification($notifiable) {
        $data = $this->getResolvedData();

        return (new NotificationMessage)
            ->types(['notification'])
            ->template('notification')
            ->subjectData($data)
            ->viewData($data);
    }

    /**
     * Returns alert message.
     *
     * @param $notifiable
     * @return NotificationMessage
     */
    public function toAlert($notifiable) {
        $data = $this->getResolvedData();

        return (new NotificationMessage)
            ->types(['alert'])
            ->template('alert')
            ->subjectData($data)
            ->viewData($data);
    }

    /**
     * Returns data from this object.
     *
     * @return array
     */
    private function getResolvedData() : array {
        return get_object_vars($this);
    }

}