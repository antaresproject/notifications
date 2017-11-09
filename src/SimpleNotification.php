<?php

namespace Antares\Notifications;

use Antares\Notifications\Messages\SmsMessage;
use Antares\Notifications\Model\SimpleContent;
use Antares\Notifications\Parsers\ContentParser;
use Illuminate\Notifications\Notification;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Messages\MailMessage;

class SimpleNotification extends Notification {

    /**
     * @var string
     */
    protected $type;

    /**
     * @var ContentParser
     */
    protected $content;

    /**
     * EventNotification constructor.
     * @param string $type
     * @param SimpleContent $content
     */
    public function __construct(string $type, SimpleContent $content) {
        $this->type     = $type;
        $this->content  = $content;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable) {
        return [$this->type];
    }

    /**
     * @return string
     */
    protected function getSubject() : string {
        return $this->content->title;
    }

    /**
     * @return string
     */
    protected function getContent() : string {
        return $this->content->content;
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return MailMessage
     */
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject( $this->getSubject() )
            ->view('antares/notifications::notification.simple', [
                'content' => $this->getContent()
            ]);
    }

    /**
     * Get the SMS representation of the notification.
     *
     * @param $notifiable
     * @return SmsMessage
     */
    public function toSms($notifiable)
    {
        return new SmsMessage($this->getContent());
    }

    /**
     * Get the notification representation of the notification.
     *
     * @param $notifiable
     * @return $this|Messages\AbstractMessage
     */
    public function toNotification($notifiable)
    {
        return (new NotificationMessage)
            ->subject( $this->getSubject() )
            ->view('antares/notifications::notification.simple', [
                'content' => $this->getContent()
            ]);
    }

    /**
     * Get the alert representation of the notification.
     *
     * @param $notifiable
     * @return $this|Messages\AbstractMessage
     */
    public function toAlert($notifiable)
    {
        return (new NotificationMessage)
            ->subject( $this->getSubject() )
            ->view('antares/notifications::notification.simple', [
                'content' => $this->getContent()
            ]);
    }

}