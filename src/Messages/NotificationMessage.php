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

class NotificationMessage extends AbstractMessage {

    /**
     * Notification type.
     *
     * @var string
     */
    public $type = 'notification';

    /**
     * Sets notification type as notification.
     *
     * @return NotificationMessage
     */
    public function asNotification() : self {
        $this->type = 'notification';

        return $this;
    }

    /**
     * Sets notification type as alert.
     *
     * @return NotificationMessage
     */
    public function asAlert() : self {
        $this->type = 'alert';

        return $this;
    }

    /**
     * Returns type of notification.
     *
     * @return string
     */
    public function getType() : string {
        return $this->type;
    }

}
