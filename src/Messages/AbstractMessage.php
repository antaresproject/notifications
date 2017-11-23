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
