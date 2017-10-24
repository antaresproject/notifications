<?php

namespace Antares\Notifications\Contracts;

use Antares\Notifications\Collections\TemplatesCollection;

interface NotificationEditable {

    /**
     * @return TemplatesCollection
     */
    public static function templates() : TemplatesCollection;

}