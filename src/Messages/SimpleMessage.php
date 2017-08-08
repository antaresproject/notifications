<?php

namespace Antares\Notifications\Messages;

use Illuminate\Notifications\Messages\SimpleMessage as LaravelSimpleMessage;

class SimpleMessage extends LaravelSimpleMessage
{

    /**
     * Message category
     *
     * @var String
     */
    public $category = 'default';

    /**
     * Message severity
     *
     * @var String 
     */
    public $severity = 'medium';

    /**
     * Raw subject without translation
     *
     * @var String 
     */
    public $rawSubject;

    /**
     * Subject params
     *
     * @var array
     */
    public $subjectParams = [];

    /**
     * The view to be rendered.
     *
     * @var array|string
     */
    public $view;

    /**
     * The view data for the message.
     *
     * @var array
     */
    public $viewData = [];

    /**
     * Priority level of the message.
     *
     * @var int
     */
    public $priority;

    /**
     * {@inheritdoc}
     */
    public function __call($name, $arguments)
    {
        if (isset($this->$name)) {
            $this->$name = $arguments[0];
            return $this;
        }
    }

    /**
     * Set the view for the mail message.
     *
     * @param  array|string  $view
     * @param  array  $data
     * @return $this
     */
    public function view($view, array $data = [])
    {
        $this->view     = $view;
        $this->viewData = $data;

        $this->markdown = null;

        return $this;
    }

    /**
     * Set the priority of this message.
     *
     * The value is an integer where 1 is the highest priority and 5 is the lowest.
     *
     * @param  int  $level
     * @return $this
     */
    public function priority($level)
    {
        $this->priority = $level;

        return $this;
    }

    /**
     * Get the data array for the mail message.
     *
     * @return array
     */
    public function data()
    {
        return array_merge($this->toArray(), $this->viewData);
    }

}
