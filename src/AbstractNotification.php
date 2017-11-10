<?php

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

    public function toMail($notifiable) {
        $data = $this->getResolvedData();

        return (new MailMessage)
            ->template('mail')
            ->subjectData($data)
            ->viewData($data);
    }

    public function toSms($notifiable) {
        $data = $this->getResolvedData();

        return (new SmsMessage())
            ->template('sms')
            ->viewData($data);
    }

    public function toNotification($notifiable) {
        $data = $this->getResolvedData();

        return (new NotificationMessage)
            ->types(['notification'])
            ->template('notification')
            ->subjectData($data)
            ->viewData($data);
    }

    public function toAlert($notifiable) {
        $data = $this->getResolvedData();

        return (new NotificationMessage)
            ->types(['alert'])
            ->template('alert')
            ->subjectData($data)
            ->viewData($data);
    }

    /**
     * @return array
     */
    private function getResolvedData() : array {
        return get_object_vars($this);
    }

}