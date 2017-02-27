<?php

/**
 * Part of the Antares Project package.
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
 * @copyright  (c) 2017, Antares Project
 * @link       http://antaresproject.io
 */

namespace Antares\Notifications\Http\Datatables;

use Antares\Notifications\Model\NotificationTypes;
use Antares\Datatables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Antares\Support\Facades\Form;
use Antares\Notifications\Filter\DateRangeNotificationLogsFilter;
use Antares\Notifications\Filter\NotificationNameFilter;
use Antares\Notifications\Filter\NotificationLangFilter;
use Antares\Notifications\Filter\NotificationAreaFilter;

class Logs extends DataTable
{

    /**
     * Available filters
     *
     * @var array 
     */
    protected $filters = [
        DateRangeNotificationLogsFilter::class,
        NotificationNameFilter::class,
        NotificationLangFilter::class,
        NotificationAreaFilter::class
    ];

    /**
     * items per page
     *
     * @var mixed 
     */
    public $perPage = 25;

    /**
     * @return Builder
     */
    public function query()
    {
        $builder = app(\Antares\Notifications\Model\NotificationsStack::class)
                ->distinct()
                ->select(['tbl_notifications_stack.*'])
                ->with('content.lang')
                ->with('notification.type')
                ->where(function ($query) {
                    $query
                    ->whereNull('author_id')
                    ->orWhere('author_id', user()->id)
                    ->orWhereHas('author', function($subquery) {
                        $subquery->whereHas('roles', function($rolesQuery) {
                            $rolesQuery->whereIn('tbl_roles.id', user()->roles->first()->getChilds());
                        });
                    });
                })
                ->with('author')
                ->with('author.roles')
                ->with('content')->with('notification.severity')
                ->orderBy('tbl_notifications_stack.created_at', 'desc');

        return $builder;
    }

    /**
     * {@inheritdoc}
     */
    public function ajax()
    {
        return $this->prepare()
                        ->editColumn('content.0.lang.code', function ($row = null) {
                            $lang     = $row->content[0]->lang;
                            $code     = $lang->code;
                            $codeIcon = (($code == 'en') ? 'us' : $code);
                            return '<i data-tooltip-inline="' . $lang->name . '" class="flag-icon flag-icon-' . $codeIcon . '"></i>';
                        })
                        ->editColumn('author.roles.0.area', function ($row = null) {
                            $area = $row->author->roles[0]->area;
                            return config('areas.areas.' . $area);
                        })
                        ->editColumn('author.fullname', function ($row = null) {
                            $title = '#' . $row->author->id . ' ' . $row->author->fullname;
                            return app('html')->link(handles('antares/foundation::users/' . $row->author->id), $title)->get();
                        })
                        ->addColumn('action', $this->getActionsColumn())
                        ->make(true);
    }

    /**
     * {@inheritdoc}
     */
    public function html()
    {
        $html = app('html');
        publish('notifications', ['js/notifications-table.js']);
        return $this->setName('Notifications List')
                        ->addColumn(['data' => 'id', 'name' => 'id', 'data' => 'id', 'title' => 'Id'])
                        ->addColumn(['data' => 'created_at', 'name' => 'created_at', 'title' => trans('antares/notifications::logs.headers.date'), 'className' => 'bolded'])
                        ->addColumn(['data' => 'notification.event', 'name' => 'notification.event', 'title' => trans('antares/notifications::logs.headers.name')])
                        ->addColumn(['data' => 'content.0.lang.code', 'name' => 'lang', 'title' => trans('antares/notifications::logs.headers.lang')])
                        ->addColumn(['data' => 'content.0.title', 'name' => 'content.0.code', 'title' => trans('antares/notifications::logs.headers.title')])
                        ->addColumn(['data' => 'notification.type.title', 'name' => 'notification.type.title', 'title' => trans('antares/notifications::logs.headers.type')])
                        ->addColumn(['data' => 'author.roles.0.area', 'name' => 'author.roles.0.area', 'title' => trans('antares/notifications::logs.headers.level')])
                        ->addColumn(['data' => 'author.fullname', 'name' => 'author.fullname', 'title' => trans('antares/notifications::logs.headers.user')])
                        ->addAction(['name' => 'edit', 'title' => '', 'class' => 'mass-actions dt-actions', 'orderable' => false, 'searchable' => false])
                        ->addGroupSelect($this->typesSelect())
                        ->addMassAction('delete', $html->link(handles('antares/notifications::logs/delete', ['csrf' => true]), $html->raw('<i class="zmdi zmdi-delete"></i><span>' . trans('antares/notification::logs.actions.delete') . '</span>'), [
                                    'class'            => "triggerable confirm mass-action",
                                    'data-title'       => trans("antares/notification::logs.are_you_sure"),
                                    'data-description' => trans("antares/notification::logs.mass_deleteing_notification_logs_desc"),
        ]));
    }

    /**
     * Creates select for types
     * 
     * @return String
     */
    protected function typesSelect()
    {
        $options = array_merge(['' => 'All'], NotificationTypes::all(['id', 'title'])->lists('title', 'id')->toArray());
        return Form::select('type', $options, null, ['data-prefix' => trans('antares/notifications::messages.datatables.select_type'), 'data-selectar--mdl-big' => "true", 'class' => 'notifications-select-type select2--prefix mr24']);
    }

    /**
     * Get actions column for table builder.
     * 
     * @return callable
     */
    protected function getActionsColumn()
    {
        return function ($row) {
            $html    = app('html');
            $btns    = [
                $html->create('li', $html->link(handles("antares::notifications/logs/preview/" . $row->id), trans('antares/notification::logs.actions.preview'), ['data-icon' => 'desktop-windows'])),
                $html->create('li', $html->link(handles("antares::notifications/logs/" . $row->id . "/delete"), trans('antares/notification::logs.actions.delete'), ['class' => "triggerable confirm", 'data-icon' => 'delete', 'data-title' => trans("antares/notification::logs.are_you_sure"), 'data-description' => trans('antares/notification::logs.delete_notification_log_desc', ['id' => $row->id])]))
            ];
            $section = $html->create('div', $html->create('section', $html->create('ul', $html->raw(implode('', $btns)))), ['class' => 'mass-actions-menu'])->get();

            return '<i class="zmdi zmdi-more"></i>' . $html->raw($section)->get();
        };
    }

}
