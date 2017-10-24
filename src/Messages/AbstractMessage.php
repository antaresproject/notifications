<?php

namespace Antares\Notifications\Messages;

use Antares\Notifications\Contracts\MessageContract;
use Antares\Notifications\Contracts\TemplateMessageContract;
use Antares\Notifications\Traits\DeliveredTemplateTrait;

abstract class AbstractMessage implements MessageContract, TemplateMessageContract {

    use DeliveredTemplateTrait;

    /**
     * The subject of the notification.
     *
     * @var string
     */
    public $subject;

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
     * Sets subject for the message.
     *
     * @param string $subject
     * @return AbstractMessage
     */
    public function subject(string $subject) : self {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Sets the view for the message.
     *
     * @param  string  $view
     * @param  array  $data
     * @return $this
     */
    public function view(string $view, array $data = []) : self {
        $this->view     = $view;
        $this->viewData = $data;

        return $this;
    }

}
