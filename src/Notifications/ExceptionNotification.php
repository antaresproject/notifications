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

namespace Antares\Notifications\Notifications;

use Antares\Notifications\Collections\TemplatesCollection;
use Antares\Notifications\Contracts\NotificationEditable;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Model\Template;
use Exception;
use Illuminate\Notifications\Notification;

class ExceptionNotification extends Notification implements NotificationEditable {

    /**
     * Exception instance.
     *
     * @var Exception
     */
    protected $exception;

    /**
     * Custom exception message for notification.
     *
     * @var string
     */
    protected $customMessage;

    /**
     * ExceptionNotification constructor.
     * @param Exception $exception
     * @param string $customMessage
     */
    public function __construct(Exception $exception, string $customMessage = '') {
        $this->exception        = $exception;
        $this->customMessage    = $customMessage;
    }

    /**
     * Returns collection of defined templates.
     *
     * @return TemplatesCollection
     */
    public static function templates() : TemplatesCollection {
        return TemplatesCollection::make('On Exception Occurred', 'system')
            ->define(self::alertMessage());
    }

    /**
     * Returns template for alert.
     *
     * @return Template
     */
    protected static function alertMessage() {
        $subject    = 'Exception has been occurred';
        $view       = 'antares/notifications::notification.exception';

        return (new Template(['alert'], $subject, $view))->setRecipients(['admin'])->setSeverity('high');
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable) {
        return ['alert'];
    }

    /**
     * Returns message for alert.
     *
     * @param $notifiable
     * @return NotificationMessage
     */
    public function toAlert($notifiable) {
        return (new NotificationMessage())
            ->asAlert()
            ->viewData([
                'message' => $this->customMessage ?: $this->exception->getMessage()
            ]);
    }

}