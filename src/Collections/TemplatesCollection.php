<?php

namespace Antares\Notifications\Collections;

use Antares\Notifications\Model\Template;
use Illuminate\Support\Arr;

class TemplatesCollection {

    /**
     * @var Template[]
     */
    protected $templates = [];

    /**
     * @return TemplatesCollection
     */
    public static function make() : TemplatesCollection {
        return new TemplatesCollection;
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

}
