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

namespace Antares\Notifications\Services;

use Antares\Model\User;
use Antares\Notifications\Notifications\ExceptionNotification;
use Exception;
use Antares\Notifications\Facade\Notification;

class ExceptionService {

    /**
     * Send notification for the given exception.
     *
     * @param Exception $exception
     * @param string $customMessage
     */
    public static function report(Exception $exception, string $customMessage = '') {
        $admins = User::administrators()->get();

        Notification::send($admins, new ExceptionNotification($exception, $customMessage));
    }

}