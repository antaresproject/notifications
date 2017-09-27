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

use Antares\Notifications\Contracts\NotificationEditable;
use Antares\Notifications\Model\NotificationsStackParams;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Model\NotificationsStack;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Services\TemplateBuilderService;
use Antares\Notifications\Synchronizer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;

class NotificationChannel
{

    /**
     * @var Synchronizer
     */
    protected $synchronizer;

    /**
     * NotificationChannel constructor.
     * @param Synchronizer $synchronizer
     */
    public function __construct(Synchronizer $synchronizer) {
        $this->synchronizer = $synchronizer;
    }

    /**
     * Send the given notification.
     *
     * @param $notifiable
     * @param Notification $notification
     */
    public function send($notifiable, Notification $notification)
    {
        DB::beginTransaction();

        try {
            $via     = $notification->via($notifiable);
            $message = array_search('alert', $via) !== false
                ? $notification->toAlert($notifiable)
                : $notification->toNotification($notifiable);

            (new TemplateBuilderService($notification))->build($message);

            $this->sendNotification($message, $notification, $notifiable->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::emergency($e);
        }
    }

    /**
     * Sends notification - add row to database.
     *
     * @param NotificationMessage $message
     * @param Notification $notification
     * @param int $modelId
     * @return Notifications
     */
    protected function sendNotification(NotificationMessage $message, Notification $notification, int $modelId)
    {
        $notificationClassName = get_class($notification);

        foreach ($message->types as $type) {
            $model     = $this->findNotification($message, $type, $notificationClassName);
            $variables = array_merge($message->subjectData, $message->viewData);

            if( ! $model && $notification instanceof NotificationEditable) {
                $this->synchronizer->syncTemplates(get_class($notification), $notification::templates());

                $model = $this->findNotification($message, $type, $notificationClassName);
            }

            if($model) {
                $this->saveInStack($model, $modelId, $variables);
            }
        }
    }

    /**
     * Saves notification in stack.
     *
     * @param Notifications $model
     * @param int $modelId
     * @param array $variables
     */
    protected function saveInStack(Notifications $model, int $modelId, array $variables = [])
    {
        $stack = new NotificationsStack([
            'notification_id' => $model->id,
            'author_id'       => auth()->guest() ? null : user()->id,
            'variables'       => $variables,
        ]);

        $stack->save();

        $stackParams = new NotificationsStackParams([
            'stack_id' => $stack->id,
            'model_id' => $modelId
        ]);

        $stack->params()->save($stackParams);
    }

    /**
     * Finds notification entry
     *
     * @param NotificationMessage $message
     * @param string $type
     * @param string $className
     * @return Notifications
     */
    protected function findNotification(NotificationMessage $message, string $type, string $className)
    {
        /* @var $notification Notifications */
        $notification = Notifications::query()
            ->whereHas('type', function(Builder $query) use($type) {
                $query->where('name', $type);
            })->whereHas('severity', function(Builder $query) use($message) {
                $query->where('name', $message->severity);
            })->whereHas('category', function(Builder $query) use($message) {
                $query->where('name', $message->category);
            })
            ->where('classname', $className)
            ->first();

        return $notification;
    }

}
