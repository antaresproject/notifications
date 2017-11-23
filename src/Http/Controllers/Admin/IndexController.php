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
     * Index action.
     *
     * @param NotificationsDataTable $dataTable
     * @return array
     */
    public function index(NotificationsDataTable $dataTable) {
        return $dataTable->render('antares/notifications::admin.index.index');
    }

    /**
     * Create action.
     *
     * @param NotificationForm $form
     * @return \Illuminate\Contracts\View\View
     */
    public function create(NotificationForm $form) {
        $form = $form->build(new Notifications);

        return view()->make('antares/notifications::admin.index.create', compact('form'));
    }

    /**
     * Store action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function store(Request $request) {
        $langCode = $request->get('lang_code', locale());

        $rules = [
            'name'                                  => 'required|max:255',
            'category'                              => 'required',
            'type_id'                               => 'required|integer|exists:tbl_notification_types,id',
            'contents'                              => 'array',
            'contents.' . $langCode . '.title'      => 'required_unless:type_id,2|max:255',
            'contents.' . $langCode . '.content'    => 'required',
        ];

        $messages = [
            'contents.*.title.required_unless'  => 'Title is required',
            'contents.*.content.required'       => 'Content is required',
        ];

        $this->validate($request, $rules, $messages);

        return $this->processor->store($request->all())->notify()->resolve($request);
    }

    /**
     * Edit action.
     *
     * @param Notifications $notification
     * @param NotificationForm $form
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Notifications $notification, NotificationForm $form) {
        $notification->load('contents', 'type', 'severity');

        $form = $form->build($notification);

        return view()->make('antares/notifications::admin.index.edit', compact('form'));
    }

    /**
     * Update action.
     *
     * @param Notifications $notification
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function update(Notifications $notification, Request $request) {
        $langCode = $request->get('lang_code', locale());

        $rules = [
            'name'                                  => 'required|max:255',
            'category'                              => 'required',
            'type_id'                               => 'required|integer|exists:tbl_notification_types,id',
            'contents'                              => 'array',
            'contents.' . $langCode . '.title'      => 'required_unless:type_id,2|max:255',
            'contents.' . $langCode . '.content'    => 'required',
        ];

        $messages = [
            'contents.*.title.required_unless'  => 'Title is required',
            'contents.*.content.required'       => 'Content is required',
        ];

        $this->validate($request, $rules, $messages);

        return $this->processor->update($notification, $request->all())->notify()->resolve($request);
    }

    /**
     * Destroy action.
     *
     * @param Request $request
     * @param Notifications $notification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function destroy(Request $request, Notifications $notification) {
        return $this->processor->delete($notification)->notify()->resolve($request);
    }

    /**
     * Send test action for one notification.
     *
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
     * Send test action for mass notifications.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendTest(Request $request) {
        return $this->processor->sendTest($request->all())->notify()->resolve($request);
    }

    /**
     * Preview action.
     *
     * @param Request $request
     * @return View
     */
    public function preview(Request $request) {
        return $this->processor->preview($request->all());
    }

    /**
     * Change status (enable/disable) action.
     *
     * @param Request $request
     * @param Notifications $notification
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function changeStatus(Request $request, Notifications $notification) {
        return $this->processor->changeStatus($notification)->notify()->resolve($request);
    }

    /**
     * Mass disable action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function massDisable(Request $request) {
        $ids = $request->input('ids', []);

        return $this->processor->massChangeStatus($ids, false)->notify()->resolve($request);
    }

    /**
     * Mass enable action.
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function massEnable(Request $request) {
        $ids = $request->input('ids', []);

        return $this->processor->massChangeStatus($ids, true)->notify()->resolve($request);
    }


}
