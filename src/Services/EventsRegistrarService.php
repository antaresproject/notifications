<?php

namespace Antares\Notifications\Services;

use Antares\Notifications\Model\NotifiableEvent;
use Illuminate\Support\Collection;

class EventsRegistrarService
{

    /**
     * @var NotifiableEvent[]
     */
    protected $events = [];

    /**
     * @var Collection
     */
    protected $models;

    /**
     * @param NotifiableEvent $event
     */
    public function register(NotifiableEvent $event)
    {
        $this->events[$event->getEventClass()] = $event;
    }

    /**
     * @return NotifiableEvent[]
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * @param string $className
     * @return NotifiableEvent|null
     */
    public function getByClassName(string $className)
    {
        if (array_key_exists($className, $this->events)) {
            return $this->events[$className];
        }

        return null;
    }

    /**
     * @return Collection
     */
    public function getModels(): Collection
    {
        if ($this->models === null) {
            $this->models = new Collection();

            foreach ($this->events as $notifiableEvent) {
                $this->models->push($notifiableEvent->toArray());
            }

            $this->models = $this->models->sortBy('label');
        }

        return $this->models->values();
    }

}
