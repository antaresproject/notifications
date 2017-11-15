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

namespace Antares\Notifications\Channels;

use Antares\Notifications\ChannelManager;
use Antares\Notifications\Model\Notifications;
use Illuminate\Notifications\Notification;

class TemplateChannel {

    /**
     * Channel Manager instance.
     *
     * @var ChannelManager
     */
    protected $channelManager;

    /**
     * TemplateChannel constructor.
     * @param ChannelManager $channelManager
     */
    public function __construct(ChannelManager $channelManager) {
        $this->channelManager = $channelManager;
    }

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification) {
        $type = self::getViaType($notification);

        $this->channelManager->channel($type)->send($notifiable, $notification);
    }

    /**
     * Returns type from the given notification object.
     *
     * @param Notification $notification
     * @return string
     * @throws \InvalidArgumentException
     */
    public static function getViaType(Notification $notification) : string {
        if( ! property_exists($notification, 'template') ) {
            throw new \InvalidArgumentException('The given notification does not contain $template property.');
        }

        if($notification->template instanceof Notifications) {
            return $notification->template->type->name;
        }
        else {
            throw new \InvalidArgumentException('The given notification $template instance is invalid.');
        }
    }


}