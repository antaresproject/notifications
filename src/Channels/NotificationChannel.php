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

use Antares\Notifications\Model\NotificationSeverity;
use Antares\Notifications\Model\NotificationsStackParams;
use Antares\Notifications\Messages\NotificationMessage;
use Antares\Notifications\Model\NotificationsStack;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Notifications\Services\TemplateBuilderService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Exception;
use Log;

class NotificationChannel
{

    /**
     * Template builder service instance.
     *
     * @var TemplateBuilderService
     */
    protected $templateBuilderService;

    /**
     * NotificationChannel constructor.
     * @param TemplateBuilderService $templateBuilderService
     */
    public function __construct(TemplateBuilderService $templateBuilderService) {
        $this->templateBuilderService = $templateBuilderService;
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
            $via = $notification->via($notifiable);

            if( property_exists($notification, 'testable') && request()->get('test')) {
                $type = NotificationTypes::query()->where('id', request()->get('type_id'))->first()->name;
            }
            else if( array_search(TemplateChannel::class, $via) !== false ) {
                $type = TemplateChannel::getViaType($notification);
            }
            else {
                $type = ((method_exists($notification, 'toAlert') && array_search('alert', $via) !== false))
                    ? 'alert'
                    : 'notification';
            }

            $message = ($type === 'alert')
                ? $notification->toAlert($notifiable)
                : $notification->toNotification($notifiable);

            $this->templateBuilderService->setNotification($notification)->build($message);

            $this->saveInStack($message, $type, $notifiable->id);

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::emergency($e);
        }
    }

    /**
     * Saves notification in stack.
     *
     * @param NotificationMessage $message
     * @param string $type
     * @param array $
     */
    protected function saveInStack(NotificationMessage $message, string $type, int $modelId)
    {
        if( property_exists($message, 'content')) {
            $content = $message->content;
        }
        else {
            $content = Arr::get($message->getViewData(), 'content', '');
        }

        $severity   = property_exists($message, 'severity') ? $message->severity : 'medium';
        $severityId = NotificationSeverity::query()->where('name', $severity)->first()->id;
        $typeId     = NotificationTypes::query()->where('name', $type)->first()->id;

        $stack = new NotificationsStack([
            'type_id'       => $typeId,
            'severity_id'   => $severityId,
            'title'         => $message->subject,
            'content'       => $content,
            'author_id'     => auth()->guest() ? null : user()->id,
        ]);

        $stack->save();

        $stackParams = new NotificationsStackParams([
            'stack_id' => $stack->id,
            'model_id' => $modelId
        ]);

        $stack->params()->save($stackParams);
    }

}
