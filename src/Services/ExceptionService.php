<?php

namespace Antares\Notifications\Services;

use Antares\Model\User;
use Antares\Notifications\Notifications\ExceptionNotification;
use Exception;
use Antares\Notifications\Facade\Notification;

class ExceptionService {

    /**
     * @param Exception $exception
     * @param string $customMessage
     */
    public static function report(Exception $exception, string $customMessage = '') {
        $admins = User::administrators()->get();

        Notification::send($admins, new ExceptionNotification($exception, $customMessage));
    }

}