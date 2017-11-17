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
 * @package    Antares Notifications
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

class SmsMessage implements MessageContract, TemplateMessageContract {

    use DeliveredTemplateTrait;

    /**
     * The message content.
     *
     * @var string
     */
    public $content;

    /**
     * The phone number the message should be sent from.
     *
     * @var string
     */
    public $from;

    /**
     * The message type.
     *
     * @var string
     */
    public $type = 'text';

    /**
     * Returns type of notification.
     *
     * @return string
     */
    public function getType() : string {
        return 'sms';
    }

    /**
     * SmsMessage constructor.
     * @param string $content
     */
    public function __construct(string $content = '') {
        $this->content = $content;
    }

    /**
     * Set the message content.
     *
     * @param  string  $content
     * @return $this
     */
    public function content(string $content) : self {
        $this->content = $content;

        return $this;
    }

    /**
     * Set the phone number the message should be sent from.
     *
     * @param  string  $from
     * @return $this
     */
    public function from($from) : self {
        $this->from = $from;

        return $this;
    }

    /**
     * Set the message type.
     *
     * @return $this
     */
    public function unicode() : self {
        $this->type = 'unicode';

        return $this;
    }

}
