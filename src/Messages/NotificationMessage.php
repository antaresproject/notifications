<?php

namespace Antares\Notifications\Messages;

use Antares\Notifications\Contracts\Message;

class NotificationMessage extends SimpleMessage implements Message
{

    /**
     * Message type
     *
     * @var String 
     */
    public $type = 'antares';

    /**
     * {@inheritdoc}
     */
    public function subject($subject, array $params = [])
    {
        $this->rawSubject    = trans($subject);
        $this->subjectParams = $params;
        return parent::subject(trans($subject, $params));
    }

}
