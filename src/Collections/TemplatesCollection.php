<?php

namespace Antares\Notifications\Collections;

use Antares\Notifications\Model\Template;
use Antares\Notifications\Services\EventsRegistrarService;
use Illuminate\Support\Arr;

class TemplatesCollection
{

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $notifiableEvent;

    /**
     * @var string
     */
    protected $eventsCategory;

    /**
     * @var Template[]
     */
    protected $templates = [];

    /**
     * TemplatesCollection constructor.
     * @param string $title
     * @param string $notifiableEvent
     */
    public function __construct(string $title, string $notifiableEvent = '')
    {
        $this->title           = $title;
        $this->notifiableEvent = class_exists($notifiableEvent) ? $notifiableEvent : '';

        if(class_exists($notifiableEvent)) {
            /* @var $service EventsRegistrarService */
            $service = app()->make(EventsRegistrarService::class);

            $this->eventsCategory = $service->getEventsCategoryByEvent($this->notifiableEvent);
        }
        else {
            $this->eventsCategory = $notifiableEvent
                ? strtolower($notifiableEvent)
                : EventsRegistrarService::DEFAULT_CATEGORY;
        }
    }

    /**
     * @param string $title
     * @param string $notifiableEvent
     * @return TemplatesCollection
     */
    public static function make(string $title, string $notifiableEvent = ''): TemplatesCollection
    {
        return new TemplatesCollection($title, $notifiableEvent);
    }

    /**
     * @param string $name
     * @param Template $template
     * @return TemplatesCollection
     */
    public function define(string $name, Template $template): self
    {
        $this->templates[$name] = $template;

        return $this;
    }

    /**
     * @param string $name
     * @return Template|null
     */
    public function getByName(string $name)
    {
        return Arr::get($this->templates, $name);
    }

    /**
     * @return string
     */
    public function getEventsCategory() : string {
        return $this->eventsCategory;
    }

    /**
     * @return Template[]
     */
    public function all(): array
    {
        return $this->templates;
    }

    /**
     * @return string
     */
    public function getNotifiableEvent(): string
    {
        return $this->notifiableEvent;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

}
