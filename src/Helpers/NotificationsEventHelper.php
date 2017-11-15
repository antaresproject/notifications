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

namespace Antares\Notifications\Helpers;

use Antares\Notifications\Model\NotifiableEvent;
use Antares\Notifications\Model\Recipient;
use Antares\Notifications\Services\EventsRegistrarService;
use Closure;

class NotificationsEventHelper {

    /**
     * Registrar for events.
     *
     * @var EventsRegistrarService
     */
    protected $eventsRegistrarService;

    /**
     * Notifiable event instance.
     *
     * @var NotifiableEvent|null
     */
    protected $event;

    /**
     * Event handler.
     *
     * @var mixed|null
     */
    protected $handler;

    /**
     * List of recipients.
     *
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
     * Creates object.
     *
     * @return NotificationsEventHelper
     */
    public static function make() : NotificationsEventHelper {
        return new static;
    }

    /**
     * Creates object. Different name for chain usage.
     *
     * @return NotificationsEventHelper
     */
    public function next() : NotificationsEventHelper {
        return new static;
    }

    /**
     * Sets notifiable event.
     *
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
     * Adds recipient for event.
     *
     * @param string $area
     * @param Closure $resolver
     * @return NotificationsEventHelper
     */
    public function addRecipient(string $area, Closure $resolver) : self {
        $this->recipients[$area] = new Recipient($area, $resolver);

        return $this;
    }

    /**
     * Adds admin recipient for event.
     *
     * @param Closure $resolver
     * @return NotificationsEventHelper
     */
    public function addAdminRecipient(Closure $resolver) : self {
        return $this->addRecipient('admin', $resolver);
    }

    /**
     * Adds client recipient for event.
     *
     * @param Closure $resolver
     * @return NotificationsEventHelper
     */
    public function addClientRecipient(Closure $resolver) : self {
        return $this->addRecipient('client', $resolver);
    }

    /**
     * Sets handler for event.
     *
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