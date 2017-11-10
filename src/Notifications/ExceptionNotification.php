<?php

namespace Antares\Notifications\Notifications;

use Antares\Notifications\Collections\TemplatesCollection;
use Antares\Notifications\Contracts\NotificationEditable;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Model\Template;
use Exception;

class ExceptionNotification implements NotificationEditable {

    /**
     * @var Exception
     */
    protected $exception;

    /**
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
     * @return TemplatesCollection
     */
    public static function templates() : TemplatesCollection {
        return TemplatesCollection::make('On Exception Occurred')
            ->define('alert', self::alertMessage());
    }

    /**
     * @return Template
     */
    protected static function alertMessage() {
        $subject    = 'Exception has been occurred';
        $view       = 'antares/notifications::notification.exception';

        return (new Template(['alert'], $subject, $view))->setRecipients(['admins'])->setSeverity('high');
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
     * @param $notifiable
     * @return NotificationMessage
     */
    public function toAlert($notifiable) {
        return (new NotificationMessage())
            ->types(['alert'])
            ->viewData([
                'message' => $this->customMessage ?: $this->exception->getMessage()
            ]);
    }

}