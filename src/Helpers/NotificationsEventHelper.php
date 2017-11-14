<?php

namespace Antares\Notifications\Helpers;

use Antares\Notifications\Model\NotifiableEvent;
use Antares\Notifications\Model\Recipient;
use Antares\Notifications\Services\EventsRegistrarService;
use Closure;

class NotificationsEventHelper {

    /**
     * @var EventsRegistrarService
     */
    protected $eventsRegistrarService;

    /**
     * @var NotifiableEvent|null
     */
    protected $event;

    /**
     * @var mixed|null
     */
    protected $handler;

    /**
     * @var Recipient[]
     */
    protected $recipients = [];

    /**
     * NotificationsHelper constructor.
     */
    public function __construct() {
        $this->eventsRegistrarService = app()->make(EventsRegistrarService::class);
    }

    /**
     * @return NotificationsEventHelper
     */
    public static function make() : NotificationsEventHelper {
        return new static;
    }

    /**
     * @return NotificationsEventHelper
     */
    public function next() : NotificationsEventHelper {
        return new static;
    }

    /**
     * @param string $className
     * @param string $categoryName
     * @param string|null $label
     * @return NotificationsEventHelper
     */
    public function event(string $className, string $categoryName, string $label = null) : self {
        $this->event = new NotifiableEvent($className, $categoryName, $label);

        return $this;
    }

    /**
     * @param string $area
     * @param Closure $resolver
     * @return NotificationsEventHelper
     */
    public function addRecipient(string $area, Closure $resolver) : self {
        $this->recipients[$area] = new Recipient($area, $resolver);

        return $this;
    }

    /**
     * @param Closure $resolver
     * @return NotificationsEventHelper
     */
    public function addAdminRecipient(Closure $resolver) : self {
        return $this->addRecipient('admin', $resolver);
    }

    /**
     * @param Closure $resolver
     * @return NotificationsEventHelper
     */
    public function addClientRecipient(Closure $resolver) : self {
        return $this->addRecipient('client', $resolver);
    }

    /**
     * @param $handler
     * @return NotificationsEventHelper
     */
    public function setHandler($handler) : self  {
        $this->handler = $handler;

        return $this;
    }

    /**
     * Register new notifiable event.
     *
     * @return NotificationsEventHelper
     */
    public function register() : self {
        if( ! $this->event) {
            throw new \InvalidArgumentException('The event is not defined.');
        }

        foreach($this->recipients as $recipient) {
            $this->event->addRecipient($recipient);
        }

        if($this->handler) {
            $this->event->setHandler($this->handler);
        }

        $this->eventsRegistrarService->register($this->event);

        return $this->next();
    }

}