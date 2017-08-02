<?php

namespace Antares\Notifications\Channels;

use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable;

class MailChannel extends BaseMailChannel
{

    /**
     * The mailer implementation.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * Create a new mail channel instance.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @return void
     */
    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        if (!$notifiable->routeNotificationFor('mail')) {
            return;
        }
        $message = $notification->toMail($notifiable);
        if ($message instanceof Mailable) {
            return $message->send($this->mailer);
        }


        $this->mailer->send($this->buildView($message), $message->data(), function ($mailMessage) use ($notifiable, $notification, $message) {
            $this->buildMessage($mailMessage, $notifiable, $notification, $message);
        });
    }

    /**
     * Build the notification's view.
     *
     * @param  \Illuminate\Notifications\Messages\MailMessage  $message
     * @return void
     */
    protected function buildView($message)
    {

        if ($message->view) {
            return $message->view;
        }

        $markdown = call_user_func($this->markdownResolver);

        return [
            'html' => $markdown->render($message->markdown, $message->data()),
            'text' => $markdown->renderText($message->markdown, $message->data()),
        ];
    }

}
