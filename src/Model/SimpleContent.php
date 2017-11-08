<?php

namespace Antares\Notifications\Model;

class SimpleContent {

    /**
     * @var string
     */
    public $langCode;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string
     */
    public $content;

    /**
     * SimpleContent constructor.
     * @param string $langCode
     * @param string $title
     * @param string $content
     */
    public function __construct(string $langCode, string $title, string $content) {
        $this->langCode = $langCode;
        $this->title    = $title;
        $this->content  = $content;
    }

}