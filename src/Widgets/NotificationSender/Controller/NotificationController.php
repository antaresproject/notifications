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

namespace Antares\Notifications\Widgets\NotificationSender\Controller;

use Antares\Notifications\Widgets\NotificationSender\Form\NotificationWidgetForm;
use Antares\Foundation\Http\Controllers\AdminController;
use Antares\Notifications\Repository\Repository;
use Antares\Notifications\Model\Notifications;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Http\JsonResponse;

class NotificationController extends AdminController
{

    /**
     * Notification widget form instance
     *
     * @var NotificationWidgetForm 
     */
    protected $form;

    /**
     * Repository instance
     *
     * @var Repository
     */
    protected $repository;

    /**
     * Construct
     * 
     * @param NotificationWidgetForm $form
     * @param Repository $repository
     */
    public function __construct(NotificationWidgetForm $form, Repository $repository)
    {
        parent::__construct();
        $this->repository = $repository;
        $this->form       = $form;
    }

    /**
     * route acl access controlling
     */
    public function setupMiddleware()
    {
        $this->middleware("web");
        $this->middleware("antares.widgets");
        $this->middleware("antares.auth");
    }

    /**
     * Index action, list of notifications depends on type
     * 
     * @return JsonResponse
     */
    public function index()
    {
        return new JsonResponse($this->getNotifications());
    }

    /**
     * Send action
     * 
     * @return JsonResponse
     */
    public function send(Request $request)
    {
        $afterValidate = $request->get('afterValidate');
        $notifications = (array) $request->get('notifications', []);

        if (! $afterValidate) {
            return $this->form->get()->isValid();
        }

        $this->fire($this->findModel($notifications));

        return new JsonResponse(['message' => trans('antares/notifications::messages.widget_notification_added_to_queue')]);
    }

    /**
     * Fires notification events
     * 
     * @param Model $model
     * @return array
     */
    protected function fire(Model $model) : array
    {
        $recipient = $this->getRecipient();

        if ($recipient->phone === null) {
            $recipient->phone = config('antares/notifications::default.sms');
        }

        $params = ['variables' => ['user' => $recipient], 'recipients' => [$recipient]];

        return event($model->event, $params);
    }

    /**
     * Gets recipient for notification
     * 
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function getRecipient()
    {
        if (Input::get('test')) {
            return user();
        }
        $route = app('router')->getRoutes()->match(app('request')->create(url()->previous()));
        return (in_array('users', $route->parameterNames()) && $uid   = $route->parameter('users')) ? user()->newQuery()->findOrFail($uid) : user();
    }

    /**
     * @param array $ids
     * @return Notifications
     * @throws \Exception
     */
    protected function findModel(array $ids) : Notifications
    {
        return Notifications::whereHas('contents', function($query) use($ids) {
            $query->where('id', $ids);
        })->firstOrFail();
    }

    /**
     * Gets notifications
     * 
     * @return \Antares\Support\Collection
     */
    public function getNotifications()
    {
        $type = Input::get('type');
        return $this->repository->getNotificationContents($type);
    }

}
