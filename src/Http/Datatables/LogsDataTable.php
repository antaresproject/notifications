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

namespace Antares\Notifications\Http\Datatables;

use Antares\Datatables\Helpers\DataTableActionsHelper;
use Antares\Notifications\Filter\DateRangeNotificationLogsFilter;
use Antares\Notifications\Filter\NotificationNameFilter;
use Antares\Notifications\Filter\NotificationLangFilter;
use Antares\Notifications\Filter\NotificationAreaFilter;
use Antares\Notifications\Model\NotificationsStack;
use Antares\Notifications\Repository\StackRepository;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Datatables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder;
use HTML;

class LogsDataTable extends DataTable
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
        /* @var $repository StackRepository */
        $repository = app()->make(StackRepository::class);

        return $repository->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function ajax()
    {
        return $this->prepare()
            ->editColumn('lang_code', function ($row) {
                $code     = $row->lang_code;
                $codeIcon = (($code === 'en') ? 'us' : $code);

                return '<i data-tooltip-inline="' . $row->lang_name . '" class="flag-icon flag-icon-' . $codeIcon . '"></i>';
            })
            ->editColumn('area', function ($row) {
                $recipients = array_get($row->variables, 'recipients');
                $area       = $recipients ? user($recipients[0]['id'])->getArea() : $row->area;

                return config('areas.areas.' . $area);
            })
            ->editColumn('fullname', function ($row) {
                $recipients = array_get($row->variables, 'recipients');
                $recipients = $recipients ? $recipients[0] : [];
                $id         = !empty($recipients) ? array_get($recipients, 'id') : $row->author_id;
                $title      = '#' . $id . ' ' . array_get($recipients, 'fullname', $row->fullname);

                return app('html')->link(handles('antares/foundation::users/' . $id), $title)->get();
            })
            ->addColumn('action', $this->getActionsColumn())
            ->make(true);
    }

    /**
     * {@inheritdoc}
     */
    public function html()
    {
        publish('notifications', ['js/notification-logs.js']);

        return $this->setName('Notifications List')
            ->addColumn(['data' => 'id', 'name' => 'tbl_notifications_stack.id', 'title' => 'Id'])
            ->addColumn(['data' => 'created_at', 'name' => 'tbl_notifications_stack.created_at', 'title' => trans('antares/notifications::logs.headers.date'), 'className' => 'bolded'])
            ->addColumn(['data' => 'name', 'name' => 'tbl_notifications.name', 'title' => trans('antares/notifications::logs.headers.name')])
            ->addColumn(['data' => 'lang_code', 'name' => 'tbl_languages.code', 'title' => trans('antares/notifications::logs.headers.lang')])
            ->addColumn(['data' => 'event', 'name' => 'tbl_notifications.event', 'title' => trans('antares/notifications::logs.headers.event')])
            ->addColumn(['data' => 'type', 'name' => 'tbl_notification_types.title', 'title' => trans('antares/notifications::logs.headers.type')])
            ->addColumn(['data' => 'area', 'name' => 'area', 'title' => trans('antares/notifications::logs.headers.level')])
            ->addColumn(['data' => 'fullname', 'name' => 'tbl_users.firstname', 'title' => trans('antares/notifications::logs.headers.user')])
            ->addAction(['name' => 'edit', 'title' => '', 'class' => 'mass-actions dt-actions', 'orderable' => false, 'searchable' => false])
            ->addGroupSelect($this->types(), 5, null, ['data-prefix' => trans('antares/notifications::messages.datatables.select_type')])
            ->setDeferedData()
            ->addMassAction('delete', $this->getMassActionButton())
            ->parameters([
                'order'        => [[1, 'desc']],
                'aoColumnDefs' => [
                    ['width' => '1%', 'targets' => 0],
                    ['width' => '7%', 'targets' => 1],
                    ['width' => '14%', 'targets' => 2],
                    ['width' => '2%', 'targets' => 3],
                    ['width' => '7%', 'targets' => 5],
                    ['width' => '5%', 'targets' => 6],
                    ['width' => '10%', 'targets' => 7],
                    ['width' => '1%', 'targets' => 8],
                ]
            ]);
    }

    /**
     * @return string|\Antares\Support\Expression
     */
    protected function getMassActionButton() {
        $url    = handles('antares::notifications/logs/delete', ['csrf' => true]);
        $button = HTML::raw('<i class="zmdi zmdi-delete"></i><span>' . trans('antares/notifications::logs.actions.delete') . '</span>');

        return HTML::link($url, $button, [
            'class'            => "triggerable confirm mass-action",
            'data-title'       => trans("antares/notifications::logs.are_you_sure"),
            'data-description' => trans("antares/notifications::logs.mass_deleteing_notification_logs_desc"),
        ]);
    }

    /**
     * Creates select for types
     * 
     * @return array
     */
    protected function types(): array {
        $types = NotificationTypes::all(['name', 'title'])->pluck('title', 'name')->toArray();

        return array_merge(['' => 'All'], $types);
    }

    /**
     * Get actions column for table builder.
     *
     * @return \Closure
     */
    protected function getActionsColumn()
    {
        return function (NotificationsStack $stack) {
            $contextMenu = DataTableActionsHelper::make();

            $contextMenu->addAction(
                handles('antares::notifications/logs/' . $stack->id . '/preview'),
                trans('antares/notifications::logs.actions.preview'), [
                    'data-icon'         => 'desktop-windows',
                    'class'             => 'triggerable preview-notification-log',
                    'data-notification' => !in_array($stack->type, ['Email', 'Sms']),
                ]
            );

            $contextMenu->addDeleteAction(
                handles('antares::notifications/logs/' . $stack->id . '/delete'),
                trans('antares/notifications::logs.actions.delete'), [
                    'data-title'        => trans('antares/notifications::logs.are_you_sure'),
                    'data-description'  => trans('antares/notifications::logs.delete_notification_log_desc', ['id' => $stack->id]),
                ]
            );

            return $contextMenu->build($stack->id);
        };
    }

}
