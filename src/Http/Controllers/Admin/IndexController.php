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

namespace Antares\Notifications\Http\Controllers\Admin;

use Antares\UI\Navigation\Breadcrumbs\Manager;
use Antares\Notifications\Http\Datatables\NotificationsDataTable;
use Antares\Notifications\Http\Form\NotificationForm;
use Antares\Notifications\Model\Notifications;
use Antares\Notifications\Processor\IndexProcessor as Processor;
use Antares\Foundation\Http\Controllers\AdminController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\View\View;

class IndexController extends AdminController
{

    use ValidatesRequests;

    /**
     * IndexController constructor.
     * @param Processor $processor
     * @param Manager $breadcrumbs
     */
    public function __construct(Processor $processor, Manager $breadcrumbs)
    {
        parent::__construct();
        $this->processor = $processor;

        $breadcrumbs->enabled(true);
    }

    /**
     * route acl access controlling
     */
    public function setupMiddleware()
    {
        $this->middleware("antares.can:antares/notifications::notifications-details", ['only' => ['show']]);
        $this->middleware("antares.can:antares/notifications::notifications-edit", ['only' => ['edit', 'update']]);
        $this->middleware("antares.can:antares/notifications::notifications-preview", ['only' => ['preview']]);
        $this->middleware("antares.can:antares/notifications::notifications-test", ['only' => ['sendtest']]);
        $this->middleware("antares.can:antares/notifications::notifications-change-status", ['only' => ['changeStatus']]);
        $this->middleware("antares.can:antares/notifications::notifications-create", ['only' => ['create', 'store']]);
        $this->middleware("antares.can:antares/notifications::notifications-list", ['only' => ['index']]);
        //$this->middleware("antares.can:antares/notifications::notifications-delete", ['only' => ['delete']]);
    }

    /**
     * @param NotificationsDataTable $dataTable
     * @return array
     */
    public function index(NotificationsDataTable $dataTable) {
        return $dataTable->render('antares/notifications::admin.index.index');
    }

    /**
     * @param NotificationForm $form
     * @return \Illuminate\Contracts\View\View
     */
    public function create(NotificationForm $form) {
        $form = $form->build(new Notifications);

        return view()->make('antares/notifications::admin.index.create', compact('form'));
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request) {
        $rules = [
            'name'                  => 'required|max:255',
            'category_id'           => 'required|integer|exists:tbl_notification_categories,id',
            'type_id'               => 'required|integer|exists:tbl_notification_types,id',
            'severity_id'           => 'required|integer|exists:tbl_notification_severity,id',
            'contents'              => 'array',
            'contents.*.title'      => 'required_unless:type_id,2|max:255',
            'contents.*.content'    => 'required',
        ];

        $messages = [
            'contents.*.title.required_unless'  => 'Title is required',
            'contents.*.content.required'       => 'Content is required',
        ];

        $this->validate($request, $rules, $messages);

        return $this->processor->store($request->all())->notify()->resolve($request);
    }

    /**
     * @param Notifications $notification
     * @param NotificationForm $form
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Notifications $notification, NotificationForm $form) {
        $notification->load('contents', 'category', 'type', 'severity');

        $form = $form->build($notification);

        return view()->make('antares/notifications::admin.index.edit', compact('form'));
    }

    /**
     * @param Notifications $notification
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(Notifications $notification, Request $request) {
        $rules = [
            'name'                  => 'required|max:255',
            'category_id'           => 'required|integer|exists:tbl_notification_categories,id',
            'type_id'               => 'required|integer|exists:tbl_notification_types,id',
            'severity_id'           => 'required|integer|exists:tbl_notification_severity,id',
            'contents'              => 'array',
            'contents.*.title'      => 'required_unless:type_id,2|max:255',
            'contents.*.content'    => 'required',
        ];

        $messages = [
            'contents.*.title.required_unless'  => 'Title is required',
            'contents.*.content.required'       => 'Content is required',
        ];

        $this->validate($request, $rules, $messages);

        return $this->processor->update($notification, $request->all())->notify()->resolve($request);
    }

    /**
     * @param Request $request
     * @param Notifications $notification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Request $request, Notifications $notification) {
        return $this->processor->delete($notification)->notify()->resolve($request);
    }

    /**
     * @param Request $request
     * @param Notifications $notification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendTestOfNotification(Request $request, Notifications $notification) {
        $data = $notification->toArray();

        $data['contents'] = [];
        $data['contents'][] = $notification->lang( lang() )->toArray();

        return $this->processor->sendTest($data)->notify()->resolve($request);
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendTest(Request $request) {
        return $this->processor->sendTest($request->all())->notify()->resolve($request);
    }

    /**
     * preview notification
     *
     * @param Request $request
     * @return View
     */
    public function preview(Request $request) {
        return $this->processor->preview($request->all());
    }

    /**
     * @param Request $request
     * @param Notifications $notification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeStatus(Request $request, Notifications $notification) {
        return $this->processor->changeStatus($notification)->notify()->resolve($request);
    }

}
