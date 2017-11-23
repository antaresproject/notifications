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
use Illuminate\Notifications\Messages\MailMessage as BaseMailMessage;

class MailMessage extends BaseMailMessage implements MessageContract, TemplateMessageContract {

    use DeliveredTemplateTrait;

    /**
     * Returns type of notification.
     *
     * @return string
     */
    public function getType() : string {
        return 'mail';
    }

}
