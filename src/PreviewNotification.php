<?php

namespace Antares\Notifications;

use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Parsers\ContentParser;
use Illuminate\Notifications\Notification;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Messages\MailMessage;

class PreviewNotification extends Notification {

    /**
     * @var string
     */
    protected $type;

    /**
     * @var NotificationContents
     */
    protected $notificationContent;

    /**
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * PreviewNotification constructor.
     * @param string $type
     * @param NotificationContents $notificationContent
     */
    public function __construct(string $type, NotificationContents $notificationContent) {
        if($type === 'email') {
            $type = 'mail';
        }

        $this->type = $type;
        $this->notificationContent = $notificationContent;
        $this->contentParser = app()->make(ContentParser::class);

        $this->contentParser->setPreviewMode(true);
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
        return $this->contentParser->parse($this->notificationContent->title);
    }

    /**
     * @return string
     */
    protected function getContent() : string {
        return $this->contentParser->parse($this->notificationContent->content);
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
            ->view('antares/notifications::notification.preview', [
                'content' => $this->getContent()
            ]);
    }

    /**
     * Get the alert representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return NotificationMessage
     */
    public function toNotification($notifiable)
    {
        return (new NotificationMessage)
            ->subject( $this->getSubject() )
            ->view('antares/notifications::notification.preview', [
                'content' => $this->getContent()
            ]);
    }

}