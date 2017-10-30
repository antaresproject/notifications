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

use Antares\Notifications\Contracts\TemplateMessageContract;
use Antares\Notifications\Decorator\MailDecorator;
use Antares\Notifications\Services\TemplateBuilderService;
use Illuminate\Notifications\Channels\MailChannel as BaseMailChannel;
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
     * Template builder service instance.
     *
     * @var TemplateBuilderService
     */
    protected $templateBuilderService;

    /**
     * MailChannel constructor.
     * @param Mailer $mailer
     * @param TemplateBuilderService $templateBuilderService
     */
    public function __construct(Mailer $mailer, TemplateBuilderService $templateBuilderService)
    {
        parent::__construct($mailer);

        $this->templateBuilderService = $templateBuilderService;
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

        $this->templateBuilderService->setNotification($notification)->build($message);

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
            if( ! $message instanceof TemplateMessageContract) {
                return parent::buildView($message);
            }

            $rendered = MailDecorator::decorate($message->content);

            $message->view($rendered, $message->data());

            return ['raw' => $rendered];
        }
        catch(\Exception $e) {
            Log::emergency($e);

            throw $e;
        }

    }

}
