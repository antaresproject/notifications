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
 * @version    0.9.0
 * @author     Antares Team
 * @license    BSD License (3-clause)
 * @copyright  (c) 2017, Antares
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Processor;

use Antares\Helpers\ResponseHelper;
use Antares\Notifications\Decorator\MailDecorator;
use Antares\Notifications\Exceptions\InfoException;
use Antares\Notifications\Model\NotificationContents;
use Antares\Notifications\Model\Notifications;
use Antares\Foundation\Processor\Processor;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Services\NotificationsService;
use Illuminate\Support\Arr;
use Exception;
use DB;
use Log;

class IndexProcessor extends Processor {

    /**
     * @var ContentParser
     */
    protected $contentParser;

    /**
     * IndexProcessor constructor.
     * @param ContentParser $contentParser
     */
    public function __construct(ContentParser $contentParser) {
        $this->contentParser = $contentParser;
    }

    /**
     * @param array $data
     * @return ResponseHelper
     */
    public function store(array $data) : ResponseHelper {
        $url = handles('antares::notifications');

        try {
            DB::beginTransaction();

            $contents       = Arr::get($data, 'contents', []);
            $langCode       = Arr::get($data, 'lang_code', locale());
            $notification   = new Notifications($data);

            $notification->save();

            foreach($contents as $content) {
                if( empty($content['title']) ) {
                    $content['title'] = $contents[$langCode]['title'];
                }

                if( empty($content['content']) ) {
                    $content['content'] = $contents[$langCode]['content'];
                }

                $notification->contents()->save(new NotificationContents($content));
            }

            $message    = trans('antares/notifications::messages.notification_create_success');
            $response   = ResponseHelper::success($message, $url);

            DB::commit();
        }
        catch(Exception $e) {
            Log::emergency($e);
            DB::rollBack();

            $message    = trans('antares/notifications::messages.notification_create_failed');
            $response   = ResponseHelper::error($message, $url);
        }

        return $response;
    }

    /**
     * @param Notifications $notification
     * @param array $data
     * @return ResponseHelper
     */
    public function update(Notifications $notification, array $data) : ResponseHelper {
        $url = handles('antares::notifications');

        try {
            DB::beginTransaction();

            $contents = Arr::get($data, 'contents', []);
            $langCode = Arr::get($data, 'lang_code', locale());

            $notification->load('contents');
            $notification->fill($data);
            $notification->save();

            foreach($contents as $content) {
                if( empty($content['title']) ) {
                    $content['title'] = $contents[$langCode]['title'];
                }

                if( empty($content['content']) ) {
                    $content['content'] = $contents[$langCode]['content'];
                }

                if($id = Arr::get($content, 'id')) {
                    $content = Arr::except($content, ['id', 'lang_id']);

                    NotificationContents::query()->findOrFail($id)->fill($content)->save();
                }
                else {
                    $notification->contents()->save(new NotificationContents($content));
                }
            }

            $message    = trans('antares/notifications::messages.notification_update_success');
            $response   = ResponseHelper::success($message, $url);

            DB::commit();
        }
        catch(Exception $e) {
            Log::emergency($e);
            DB::rollBack();

            $message    = trans('antares/notifications::messages.notification_update_failed');
            $response   = ResponseHelper::error($message, $url);
        }

        return $response;
    }

    /**
     * @param Notifications $notification
     * @return ResponseHelper
     */
    public function delete(Notifications $notification) : ResponseHelper {
        $url = handles('antares::notifications');

        try {
            DB::beginTransaction();

            $notification->load('contents');

            foreach($notification->contents as $content) {
                $content->delete();
            }

            $notification->delete();

            $message    = trans('antares/notifications::messages.notification_delete_success');
            $response   = ResponseHelper::success($message, $url);

            DB::commit();
        }
        catch(Exception $e) {
            Log::emergency($e);
            DB::rollBack();

            $message    = trans('antares/notifications::messages.notification_delete_failed');
            $response   = ResponseHelper::error($message, $url);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return ResponseHelper
     */
    public function sendTest(array $data) : ResponseHelper {
        try {
            $this->contentParser->setPreviewMode(true);

            $notification = new Notifications($data);

            foreach(Arr::get($data, 'contents', []) as $content) {
                $notification->contents->push(new NotificationContents($content));
            }

            /* @var $service NotificationsService */
            $service = app()->make(NotificationsService::class);

            $service->handleAsPreview($notification, auth()->user());

            $message    = trans('antares/notifications::messages.notification_preview_sent');
            $response   = ResponseHelper::success($message);
        }
        catch(InfoException $e) {
            Log::emergency($e);

            $message    = $e->getMessage();
            $response   = ResponseHelper::error($message);
        }
        catch(Exception $e) {
            Log::emergency($e);

            $message    = trans('antares/notifications::messages.notification_preview_error');
            $response   = ResponseHelper::error($message);
        }

        return $response;
    }

    /**
     * @param array $data
     * @return array
     */
    public function preview(array $data) {
        $this->contentParser->setPreviewMode(true);

        $type = Arr::get($data, 'type');

        if($type === 'sms') {
            $data['title'] = '';
        }
        else {
            $data['title'] = $this->contentParser->parse(  Arr::get($data, 'title', '') );
        }

        $data['content'] = $this->contentParser->parse(  Arr::get($data, 'content', '') );

        if($type === 'mail') {
            $data['content'] = MailDecorator::decorate($data['content']);
        }

        return $data;
    }

    /**
     * @param Notifications $notification
     * @return ResponseHelper
     */
    public function changeStatus(Notifications $notification) : ResponseHelper {
        $url = handles('antares::notifications');

        try {
            $notification->active = ! $notification->active;
            $notification->save();

            $message    = trans('antares/notifications::messages.notification_change_status_success');
            $response   = ResponseHelper::success($message, $url);
        }
        catch(Exception $e) {
            Log::emergency($e);

            $message    = trans('antares/notifications::messages.notification_change_status_failed');
            $response   = ResponseHelper::error($message);
        }

        return $response;
    }

}
