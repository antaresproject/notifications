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

    const DEFAULT_CATEGORY = 'system';

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

    /**
     * @return Collection
     */
    public function getEventsCategories() : Collection {
        $categories = [];

        foreach($this->events as $notifiableEvent) {
            $categories[] = $notifiableEvent->getCategoryName();
        }

        if( ! in_array(self::DEFAULT_CATEGORY, $categories) ) {
            $categories[] = self::DEFAULT_CATEGORY;
        }

        return Collection::make(array_unique($categories))
            ->map(function(string $category) {
                return [
                    'id'    => $category,
                    'label' => ucfirst($category),
                ];
            })
            ->sortBy('label')
            ->values();
    }

    /**
     * @param string $eventClassName
     * @return string
     */
    public function getEventsCategoryByEvent(string $eventClassName) : string {
        if($event = $this->getByClassName($eventClassName)) {
            return $event->getCategoryName();
        }

        return self::DEFAULT_CATEGORY;
    }

}
