<?php

namespace Antares\Notifications\Messages;

class NotificationMessage extends AbstractMessage {

    /**
     * @var string[]
     */
    public $types = ['admin'];

    /**
     * @param string[] $types
     * @return NotificationMessage
     */
    public function types(array $types) : self {
        $this->types = $types;

        return $this;
    }

}
