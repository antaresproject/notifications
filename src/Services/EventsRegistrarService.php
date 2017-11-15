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

use Antares\Notifications\Model\NotifiableEvent;
use Illuminate\Support\Collection;

class EventsRegistrarService
{

    /**
     * List of notifiable events.
     *
     * @var NotifiableEvent[]
     */
    protected $events = [];

    /**
     * Built collection of events.
     *
     * @var Collection
     */
    protected $models;

    /**
     * Default notification category name.
     */
    const DEFAULT_CATEGORY = 'system';

    /**
     * Registers event.
     *
     * @param NotifiableEvent $event
     */
    public function register(NotifiableEvent $event)
    {
        $this->events[$event->getEventClass()] = $event;
    }

    /**
     * Returns all registered events.
     *
     * @return NotifiableEvent[]
     */
    public function events(): array
    {
        return $this->events;
    }

    /**
     * Returns event by the given class name if exists.
     *
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
     * Returns sorted collection of events.
     *
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
     * Returns sorted categories from registered events.
     *
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
     * Returns category from event class name. If not exists then default will be returned.
     *
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
