<?php

namespace Antares\Notifications\Model;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use Closure;

class NotifiableEvent implements Arrayable {

    /**
     * @var string
     */
    protected $eventClass;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var Closure|string|null
     */
    protected $handler;

    /**
     * @var Recipient[]
     */
    protected $recipients = [];

    /**
     * @var array
     */
    protected $variables = [];

    /**
     * NotifiableEvent constructor.
     * @param string $eventClass
     * @param string|null $label
     */
    public function __construct(string $eventClass, string $label = null) {
        $this->eventClass = $eventClass;
        $this->label = $label ?: $eventClass;
    }

    /**
     * @return string
     */
    public function getEventClass() : string {
        return $this->eventClass;
    }

    /**
     * @return string
     */
    public function getLabel() : string {
        return $this->label;
    }

    /**
     * @param Closure|string $handler
     * @throws InvalidArgumentException
     */
    public function setHandler($handler) {
        if($handler instanceof Closure || is_string($handler)) {
            $this->handler = $handler;
        }
        else {
            throw new InvalidArgumentException('The handler has invalid type.');
        }
    }

    /**
     * @return null|string
     */
    public function getHandler() : ?string {
        return $this->handler;
    }

    /**
     * @param Recipient $recipient
     */
    public function addRecipient(Recipient $recipient) {
        $this->recipients[$recipient->getId()] = $recipient;
    }

    /**
     * @param string $id
     * @return Recipient|null
     */
    public function getRecipientById(string $id) : ?Recipient {
        return Arr::get($this->recipients, $id);
    }

    /**
     * @return array
     */
    public function getRecipientsLabels() : array {
        return array_map(function(Recipient $recipient) {
            return $recipient->toArray();
        }, array_values($this->recipients));
    }

    /**
     * Get the instance as an array.
     *
     * @return array
     */
    public function toArray() : array {
        return [
            'event_class'   => $this->getEventClass(),
            'label'         => $this->getLabel(),
            'recipients'    => $this->getRecipientsLabels(),
            'variables'     => $this->variables,
        ];
    }
}
