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

namespace Antares\Notifications\Collections;

use Antares\Notifications\Model\Template;
use Antares\Notifications\Services\EventsRegistrarService;
use Illuminate\Support\Arr;

class TemplatesCollection
{

    /**
     * Title of grouped templates.
     *
     * @var string
     */
    protected $title;

    /**
     * Class name or event category if event does not exists.
     *
     * @var string
     */
    protected $notifiableEvent;

    /**
     * Resolved event category name.
     *
     * @var string
     */
    protected $eventsCategory;

    /**
     * List of templates.
     *
     * @var Template[]
     */
    protected $templates = [];

    /**
     * TemplatesCollection constructor.
     * @param string $title
     * @param string $notifiableEvent Event class name or category name.
     */
    public function __construct(string $title, string $notifiableEvent)
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
     * Creates instance of class.
     *
     * @param string $title
     * @param string $notifiableEvent Event class name or category name.
     * @return TemplatesCollection
     */
    public static function make(string $title, string $notifiableEvent): TemplatesCollection
    {
        return new TemplatesCollection($title, $notifiableEvent);
    }

    /**
     * Defines template within given name.
     *
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
     * Returns template by given name. If not exists then NULL will be returned.
     *
     * @param string $name
     * @return Template|null
     */
    public function getByName(string $name)
    {
        return Arr::get($this->templates, $name);
    }

    /**
     * Returns resolved event category.
     *
     * @return string
     */
    public function getEventsCategory() : string {
        return $this->eventsCategory;
    }

    /**
     * Returns an array of templates.
     *
     * @return Template[]
     */
    public function all(): array
    {
        return $this->templates;
    }

    /**
     * Returns event class name. If class does not exists then empty string will be returned.
     *
     * @return string
     */
    public function getNotifiableEvent(): string
    {
        return $this->notifiableEvent;
    }

    /**
     * Returns title of templates group.
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

}
