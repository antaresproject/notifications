<?php

namespace Antares\Notifications;

use Antares\Notifications\Channels\TemplateChannel;
use Antares\Notifications\Model\Notifications;
use Illuminate\Notifications\Notification;

class AbstractNotification extends Notification
{

    /**
     * Is notification during test action.
     *
     * @var bool
     */
    public $testable = false;

    /**
     * Notification Template
     *
     * @var Notifications|null
     */
    public $template;

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [TemplateChannel::class];
    }

}