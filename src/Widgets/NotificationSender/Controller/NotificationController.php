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

use Antares\Model\User;
use Antares\Notifications\Parsers\ContentParser;
use Antares\Notifications\Services\NotificationsService;
use Antares\Notifications\Widgets\NotificationSender\Form\NotificationWidgetForm;
use Antares\Foundation\Http\Controllers\AdminController;
use Antares\Notifications\Repository\Repository;
use Antares\Notifications\Model\Notifications;
use Illuminate\Database\Eloquent\Builder;
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
     * NotificationController constructor.
     * @param NotificationWidgetForm $form
     * @param Repository $repository
     * @param ContentParser $contentParser
     */
    public function __construct(NotificationWidgetForm $form, Repository $repository, ContentParser $contentParser)
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
        $this->middleware("antares.auth");
    }

    /**
     * Index action, list of notifications depends on type
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request)
    {
        $contents = $this->repository->getNotificationContents( $request->get('type') );

        return response()->json($contents);
    }

    /**
     * Send action
     *
     * @param ContentParser $contentParser
     * @param NotificationsService $notificationsService
     * @param Request $request
     * @return JsonResponse|mixed
     */
    public function send(ContentParser $contentParser, NotificationsService $notificationsService, Request $request)
    {
        if (!$request->get('afterValidate')) {
            return $this->form->get()->isValid();
        }

        $contentParser->setPreviewMode(true);

        $model      = $this->findModel($request->get('notifications'));
        $recipient  = $this->getRecipient();

        $notificationsService->handleAsPreview($model, $recipient);

        return new JsonResponse(['message' => trans('antares/notifications::messages.widget_notification_added_to_queue')]);
    }

    /**
     * Gets recipient for notification
     * 
     * @return User
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
     * Finds notification model
     *
     * @param $id
     * @return Notifications
     */
    protected function findModel($id)
    {
        /* @var $model Notifications */
        $model = Notifications::query()->whereHas('contents', function(Builder $query) use($id) {
            $query->where('id', $id);
        })->firstOrFail();

        return $model;
    }

}
