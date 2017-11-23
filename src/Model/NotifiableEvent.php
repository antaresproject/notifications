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

namespace Antares\Notifications\Model;

use Antares\Notifications\Services\VariablesService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Closure;
use ReflectionClass;

class NotifiableEvent implements Arrayable {

    /**
     * @var string
     */
    protected $eventClass;

    /**
     * @var string
     */
    protected $categoryName;

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
     * @param string $categoryName
     * @param string|null $label
     * @throws InvalidArgumentException
     */
    public function __construct(string $eventClass, string $categoryName, string $label = null) {
        if( ! class_exists($eventClass) ) {
            throw new InvalidArgumentException('The [' . $eventClass . '] class does not exist.');
        }

        $this->eventClass = $eventClass;
        $this->categoryName = strtolower($categoryName);
        $this->label = $label ?: Str::title(
            Str::snake(class_basename($eventClass), ' ')
        );

        $this->assignVariablesFromEvent();
    }

    /**
     * Assign possible variables from the event class name based on
     */
    protected function assignVariablesFromEvent() {
        /* @var $variablesService VariablesService */
        $variablesService = app()->make(VariablesService::class);
        $parameters = (new ReflectionClass($this->eventClass))->getConstructor()->getParameters();

        foreach($parameters as $parameter) {
            $moduleVariables = $variablesService->firstModuleVariablesByParameter($parameter);

            if($moduleVariables) {
                $this->variables[] = $moduleVariables->toArray();
            }
        }
    }

    /**
     * Returns event class name.
     *
     * @return string
     */
    public function getEventClass() : string {
        return $this->eventClass;
    }

    /**
     * Returns category name.
     *
     * @return string
     */
    public function getCategoryName() : string {
        return $this->categoryName;
    }

    /**
     * Returns label.
     *
     * @return string
     */
    public function getLabel() : string {
        return $this->label;
    }

    /**
     * Sets handler for event as closure or class name.
     *
     * @param Closure|string $handler
     * @return NotifiableEvent
     * @throws InvalidArgumentException
     */
    public function setHandler($handler) : self {
        if($handler instanceof Closure || (is_string($handler) && class_exists($handler))) {
            $this->handler = $handler;
        }
        else {
            throw new InvalidArgumentException('The handler has invalid type.');
        }

        return $this;
    }

    /**
     * Returns defined handler if exists.
     *
     * @return Closure|null|string
     */
    public function getHandler() {
        return $this->handler;
    }

    /**
     * Adds recipient.
     *
     * @param Recipient $recipient
     * @return NotifiableEvent
     */
    public function addRecipient(Recipient $recipient) : self {
        $this->recipients[$recipient->getArea()] = $recipient;

        return $this;
    }

    /**
     * Return recipient by the given area name if exists.
     *
     * @param string $area
     * @return Recipient|null
     */
    public function getRecipientByArea(string $area) : ?Recipient {
        return Arr::get($this->recipients, $area);
    }

    /**
     * Returns an array of recipients labels.
     *
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
            'category_name' => $this->getCategoryName(),
            'label'         => $this->getLabel(),
            'recipients'    => $this->getRecipientsLabels(),
            'variables'     => $this->variables,
        ];
    }
}
