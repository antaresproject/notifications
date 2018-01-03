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

namespace Antares\Notifications\Services;

use Antares\Model\User;
use Antares\Notifications\ChannelManager;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Model\SimpleContent;
use Antares\Notifications\Notifications\ExceptionNotification;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\SimpleNotification;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use ReflectionClass;
use Log;

class NotificationsService {

    /**
     * Channel manager instance.
     *
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * Content parser instance.
     *
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * Variables service instance.
     *
     * @var VariablesService
     */
    protected $variablesService;

    /**
     * NotificationsService constructor.
     * @param ChannelManager $channelManager
     * @param ContentParser $contentParser
     * @param VariablesService $variablesService
     */
    public function __construct(ChannelManager $channelManager, ContentParser $contentParser, VariablesService $variablesService) {
        $this->channelManager   = $channelManager;
        $this->contentParser    = $contentParser;
        $this->variablesService = $variablesService;
    }

    /**
     * Handles notification as preview for given user.
     *
     * @param Notifications $notification
     * @param User $user
     */
    public function handleAsPreview(Notifications $notification, User $user) {
        $type               = $notification->type->name;
        $contentModel       = $notification->lang( lang() );
        $titleLine          = $this->contentParser->parse($contentModel->title);
        $contentLine        = $this->contentParser->parse($contentModel->content);
        $content            = new SimpleContent($contentModel->lang->code, $titleLine, $contentLine);
        $notificationToSend = new SimpleNotification($type, $content);

        $notificationToSend->testable = true;
        $notificationToSend->template = $notification;

        $this->channelManager->send($user, $notificationToSend);
    }

    /**
     * Handles notification for given event.
     *
     * @param Notifications $notification
     * @param object $event
     */
    public function handle(Notifications $notification, $event) {
        $eventModel = $notification->event_model;
        $notificationToSend = null;

        if(! is_object($eventModel)) {
            return;
        }

        if($handler = $eventModel->getHandler()) {
            $notificationToSend = app()->make($handler)->handle($event, $notification);
        }

        if($handler && ! $notificationToSend instanceof Notification) {
            return;
        }

        if($source = $notification->source) {
            $notificationToSend = $this->getNotificationObjectBySource($source, $event);
        }
        else {
            $type               = $notification->type->name;
            $contentModel       = $notification->lang( lang() );
            $titleLine          = $this->contentParser->parse($contentModel->title);
            $contentLine        = $this->contentParser->parse($contentModel->content);
            $content            = new SimpleContent($contentModel->lang->code, $titleLine, $contentLine);
            $notificationToSend = new SimpleNotification($type, $content);
        }

        $notificationToSend->template = $notification;

        $resolvedRecipients = new Collection();

        foreach( (array) $notification->recipients as $recipientArea) {
            $recipient = $eventModel->getRecipientByArea($recipientArea);

            if($recipient && ($resolvedRecipient = $recipient->resolve($event))) {
                if($resolvedRecipient instanceof Collection || is_array($resolvedRecipient)) {
                    foreach($resolvedRecipient as $singleRecipient) {
                        $resolvedRecipients->push($singleRecipient);
                    }
                }
                else {
                    $resolvedRecipients->push($resolvedRecipient);
                }
            }
        }

        try {
            $this->channelManager->send($resolvedRecipients->unique(), $notificationToSend);
        }
        catch(\Exception $e) {
            Log::emergency($e);

            $message = 'Error occurred while sending notification. Please check logfile for more details.';
            $exceptionNotification = new ExceptionNotification($e, $message);

            /* @var $user User */
            if( ($user = auth()->user()) && \Auth::isNot(['member', 'guest', 'client'])) {
                $notifiable = $user;
            }
            else {
                $notifiable = User::administrators()->get();
            }

            $this->channelManager->send($notifiable, $exceptionNotification);
        }

    }

    /**
     * Returns resolved notification object of source.
     *
     * @param string $source
     * @param object|null $event
     * @return Notification
     */
    public function getNotificationObjectBySource(string $source, $event = null) : Notification {
        $parameters = (new ReflectionClass($source))->getConstructor()->getParameters();
        $parametersToPass = [];

        foreach($parameters as $parameter) {
            $name = $parameter->getName();

            if(is_object($event) && property_exists($event, $name)) {
                $parametersToPass[$name] = $event->{$name};
            }
            else {
                $parametersToPass[$name] = $this->variablesService->getDefault($parameter);
            }
        }

        $object = app()->makeWith($source, $parametersToPass);

        if( ! $object instanceof Notification) {
            throw new \DomainException('The notification source name have resolved object which is not supported.');
        }

        return $object;
    }

}