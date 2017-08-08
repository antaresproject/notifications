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

use Antares\Notifications\Model\NotificationsStackParams;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Model\NotificationsStack;
use Antares\Notifications\Model\Notifications;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Exception;

class NotificationChannel
{

    /**
     * Send the given notification.
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return boolean
     */
    public function send($notifiable, Notification $notification)
    {
        DB::beginTransaction();
        try {
            $via     = $notification->via($notifiable);
            $message = array_search('alert', $via) !== false ? $notification->toAlert($notifiable) : $notification->toNotification($notifiable);
            $this->sendNotification($message, $notification, $notifiable->id);
        } catch (Exception $ex) {
            DB::rollback();
            return false;
        }
        DB::commit();
        return true;
    }

    /**
     * Sends notification - add row to database
     * 
     * @param NotificationMessage $message
     * @param Notification $notification
     * @param mixed $uid
     */
    protected function sendNotification(NotificationMessage $message, Notification $notification, $uid)
    {
        if (is_array($message->type)) {
            foreach ($message->type as $type) {
                $model     = $this->notification($message, $type, get_class($notification));
                $variables = array_merge($message->subjectParams, $message->viewData);
                $this->saveInStack($model, $uid, $variables);
            }
        }
        return $this->notification($message, $message->type, get_class($notification));
    }

    /**
     * Saves notification in stack
     * 
     * @param Notifications $model
     * @param mixed $uid
     * @param array $variables
     * @return Notifications
     */
    protected function saveInStack(Notifications $model, $uid, array $variables = [])
    {
        $stack       = new NotificationsStack([
            'notification_id' => $model->id,
            'author_id'       => auth()->guest() ? null : user()->id,
            'variables'       => $variables,
        ]);
        $stack->save();
        $stackParams = new NotificationsStackParams(['stack_id' => $stack->id, 'model_id' => $uid]);
        return $stack->params()->save($stackParams);
    }

    /**
     * Finds notification entry
     * 
     * @param NotificationMessage $message
     * @param String $type
     * @param String $classname
     * @return Notifications
     */
    protected function notification(NotificationMessage $message, $type, $classname)
    {
        return Notifications::query()->whereHas('type', function($subquery) use($type) {
                            $subquery->where('name', $type);
                        })->whereHas('severity', function($subquery) use($message) {
                            $subquery->where('name', $message->severity);
                        })->whereHas('category', function($subquery) use($message) {
                            $subquery->where('name', $message->category);
                        })
                        ->where(['classname' => $classname])
                        ->first();
    }

}
