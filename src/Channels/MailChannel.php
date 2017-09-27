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

use Antares\Notifications\Contracts\NotificationEditable;
use Antares\Notifications\Decorator\MailDecorator;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Services\TemplateBuilderService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Mail\Mailable;
use Log;

class MailChannel extends BaseMailChannel
{

    /**
     * The mailer implementation.
     *
     * @var \Illuminate\Contracts\Mail\Mailer
     */
    protected $mailer;

    /**
     * Instance of ContentParser
     *
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * MailChannel constructor.
     * @param Mailer $mailer
     * @param ContentParser $contentParser
     */
    public function __construct(Mailer $mailer, ContentParser $contentParser)
    {
        parent::__construct($mailer);

        $this->contentParser = $contentParser;
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

        (new TemplateBuilderService($notification))->build($message);

        if ($message instanceof Mailable) {
            $message->send($this->mailer);
            return;
        }

        if($message instanceof MailMessage) {
            $this->mailer->send($this->view($message, $notification), $message->data(), function ($mailMessage) use ($notifiable, $notification, $message) {
                $this->buildMessage($mailMessage, $notifiable, $notification, $message);
            });
        }
    }

    /**
     * Creates message view
     *
     * @param MailMessage $message
     * @param Notification $notification
     * @return array
     * @throws \Exception
     */
    protected function view(MailMessage $message, Notification $notification)
    {
        try {
            $notificationContent = $this->findNotification($message, $notification);

            if($notificationContent === null) {
                return parent::buildView($message);
            }

            $rendered = $this->contentParser->parse($notificationContent->content, $message->viewData);
            $rendered = MailDecorator::decorate($rendered);

            $message->view($rendered, $message->data());

            return ['raw' => $rendered];
        }
        catch(\Exception $e) {
            Log::emergency($e);

            throw $e;
        }

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
        if(! $notification instanceof NotificationEditable) {
            return null;
        }

        /* @var $notificationContent NotificationContents */
        $notificationContent =  NotificationContents::query()
            ->where('lang_id', lang_id())
            ->whereHas('notification', function(Builder $query) use($message, $notification) {
                $query->where([
                    'classname' => get_class($notification),
                    'active'    => 1
                ])->whereHas('category', function(Builder $query) use($message) {
                    $query->where('name', $message->category);
                })->whereHas('type', function(Builder $query) use($message) {
                    $query->whereIn('name', $message->types);
                })->whereHas('severity', function(Builder $query) use($message) {
                    $query->where('name', $message->severity);
                });
            })->first();

        return $notificationContent;
    }

}
