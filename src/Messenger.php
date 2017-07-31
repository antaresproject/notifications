<?php

namespace Antares\Notifications;

use Antares\Notifications\Contracts\Message;
use Antares\Notifications\Synchronizer;

class Messenger
{

    /**
     * Notification synchronizer instance
     *
     * @var Synchronizer 
     */
    protected $synchronizer;

    /**
     * Construct
     * 
     * @param Synchronizer $synchronizer
     */
    public function __construct(Synchronizer $synchronizer)
    {
        $this->synchronizer = $synchronizer;
    }

    /**
     * Sends notification
     * 
     * @param mixed $recipients
     * @param Message $message
     */
    public function send($recipients, Message $message)
    {
        $message->setRecipients($recipients);
        $exists = app('notifications.contents')->findByClassname(get_class($message));
        if (!$exists) {
            $this->synchronizer->insert($message);
        }
        event($message->getName(), [$message->getData(), $recipients]);
        return $message;
    }

}
