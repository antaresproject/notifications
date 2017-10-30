<?php

namespace Antares\Notifications\Services;

use Antares\Notifications\Model\NotifiableEvent;
use Illuminate\Support\Collection;

class EventsRegistrarService {

    /**
     * @var NotifiableEvent[]
     */
    protected static $events = [];

    /**
     * @var Collection
     */
    protected $models;

    /**
     * @param NotifiableEvent $event
     */
    public function register(NotifiableEvent $event) {
        static::$events[$event->getEventClass()] = $event;
    }

    /**
     * @return NotifiableEvent[]
     */
    public function events() : array {
        return static::$events;
    }

    /**
     * @param string $className
     * @return NotifiableEvent|null
     */
    public function getByClassName(string $className) : ?NotifiableEvent {
        if( array_key_exists($className, static::$events) ) {
            return static::$events[$className];
        }

        return null;
    }

    /**
     * @return Collection
     */
    public function getModels() : Collection {
        if($this->models === null) {
            $this->models = new Collection();

            foreach(static::$events as $notifiableEvent) {
                $this->models->push($notifiableEvent->toArray());
            }

            $this->models = $this->models->sortBy('label');
        }

        return $this->models->values();
    }

}