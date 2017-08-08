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

use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Adapter\VariablesAdapter;
use Antares\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Twig_Loader_String;
use Twig_Environment;

class MailChannel extends BaseMailChannel
{

    /**
     * The mailer implementation.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * Instance of NotificationContents
     *
     * @var NotificationContents 
     */
    protected $contents;

    /**
     * Instance of VariablesAdapter
     *
     * @var VariablesAdapter 
     */
    protected $variablesAdapter;

    /**
     * Create a new mail channel instance.
     *
     * @param  \Illuminate\Contracts\Mail\Mailer  $mailer
     * @param NotificationContents $contents
     * @param VariablesAdapter $variablesAdapter
     * @return void
     */
    public function __construct(Mailer $mailer, NotificationContents $contents, VariablesAdapter $variablesAdapter)
    {
        $this->mailer           = $mailer;
        $this->contents         = $contents;
        $this->variablesAdapter = $variablesAdapter;
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

        $this->mailer->send($this->view($message, $notification), $message->data(), function ($mailMessage) use ($notifiable, $notification, $message) {
            $this->buildMessage($mailMessage, $notifiable, $notification, $message);
        });
    }

    /**
     * Creates message view
     * 
     * @param MailMessage $message
     * @param Notification $notification
     * @return String
     */
    protected function view(MailMessage $message, Notification $notification)
    {

        if (!is_null($content = $this->findNotification($message, $notification))) {
            $twig = new Twig_Environment(new Twig_Loader_String());




            $html = $this->variablesAdapter->fill($content->content);

            preg_match_all('/\[\[(.*?)\]\]/', $content, $matches);
            $html = str_replace($matches[0], $matches[1], $html);

            $rendered = $twig->render($html, $message->viewData);



            $message->view($rendered);
            $values = collect($message->subjectParams)->flatMap(function($item, $key) {
                        return [':' . $key => $item];
                    })->toArray();



            $subject = str_replace(array_keys($values), array_values($values), $content->title);
            $message->subject($subject);
            return ['raw' => $rendered];
        }
        return parent::buildView($message);
    }

    /**
     * Finds notification content
     * 
     * @param MailMessage $message
     * @param Notification $notification
     * @return NotificationContents
     */
    protected function findNotification(MailMessage $message, Notification $notification)
    {
        return $this->contents->newQuery()->where([
                    'lang_id' => lang_id()
                ])->whereHas('notification', function($subquery) use($message, $notification) {
                    $subquery->where([
                        'classname' => get_class($notification),
                        'active'    => 1
                    ])->whereHas('category', function($query) use($message) {
                        $query->where('name', $message->category);
                    })->whereHas('type', function($query) use($message) {
                        $query->where('name', $message->type);
                    })->whereHas('severity', function($query) use($message) {
                        $query->where('name', $message->severity);
                    });
                })->first();
    }

}
