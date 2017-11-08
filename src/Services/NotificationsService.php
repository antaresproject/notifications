<?php

namespace Antares\Notifications\Services;

use Antares\Model\User;
use Antares\Notifications\ChannelManager;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Model\SimpleContent;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\SimpleNotification;
use Illuminate\Notifications\Notification;
use ReflectionClass;

class NotificationsService {

    /**
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * @var ContentParser
     */
    protected $contentParser;

    /**
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
     * @param Notifications $notification
     * @param User $user
     */
    public function handleAsPreview(Notifications $notification, User $user) {
        if($source = $notification->source) {
            $notificationToSend = $this->getNotificationObjectBySource($source);
        }
        else {
            $type               = $notification->type->name;
            $contentModel       = $notification->lang( lang() );
            $titleLine          = $this->contentParser->parse($contentModel->title);
            $contentLine        = $this->contentParser->parse($contentModel->content);
            $content            = new SimpleContent($contentModel->lang->code, $titleLine, $contentLine);
            $notificationToSend = new SimpleNotification($type, $content);
        }

        $notificationToSend->testable = true;
        $notificationToSend->template = $notification;

        $this->channelManager->send($user, $notificationToSend);
    }

    /**
     * @param Notifications $notification
     * @param object $event
     */
    public function handle(Notifications $notification, $event) {
        $eventModel = $notification->event_model;
        $notificationToSend = null;

        if($handler = $eventModel->getHandler()) {
            $notificationToSend = app()->make($handler)->handle($event, $notification);
        }

        if( ! $notificationToSend) {
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

        foreach( (array) $notification->recipients as $recipientId) {
            $recipient = $eventModel->getRecipientById($recipientId);

            if($recipient) {
                $this->channelManager->send($recipient->resolve($event), $notificationToSend);
            }
        }
    }

    /**
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