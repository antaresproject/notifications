<?php

namespace Antares\Notifications\Collections;

use Antares\Notifications\Model\Template;
use Illuminate\Support\Arr;

class TemplatesCollection {

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $notifiableEvent;

    /**
     * @var Template[]
     */
    protected $templates = [];

    /**
     * TemplatesCollection constructor.
     * @param string $title
     * @param string $notifiableEvent
     */
    public function __construct(string $title, string $notifiableEvent = '') {
        $this->title = $title;
        $this->notifiableEvent = $notifiableEvent;
    }

    /**
     * @param string $title
     * @param string $notifiableEvent
     * @return TemplatesCollection
     */
    public static function make(string $title, string $notifiableEvent = '') : TemplatesCollection {
        return new TemplatesCollection($title, $notifiableEvent);
    }

    /**
     * @param string $name
     * @param Template $template
     * @return TemplatesCollection
     */
    public function define(string $name, Template $template) : self {
        $this->templates[$name] = $template;

        return $this;
    }

    /**
     * @param string $name
     * @return Template|null
     */
    public function getByName(string $name) : ?Template {
        return Arr::get($this->templates, $name);
    }

    /**
     * @return Template[]
     */
    public function all() : array {
        return $this->templates;
    }

    /**
     * @return string
     */
    public function getNotifiableEvent() : string {
        return $this->notifiableEvent;
    }

    /**
     * @return string
     */
    public function getTitle() : string {
        return $this->title;
    }

}
