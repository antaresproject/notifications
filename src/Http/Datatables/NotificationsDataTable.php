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

use Antares\Area\AreaManager;
use Antares\Authorization\Authorization;
use Antares\Datatables\Helpers\DataTableActionsHelper;
use Antares\Notifications\Filter\NotificationStatusFilter;
use Antares\Notifications\Model\Notifications as NotificationsModel;
use Antares\Notifications\Filter\NotificationCategoryFilter;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Datatables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Closure;
use Yajra\Datatables\Request;

class NotificationsDataTable extends DataTable
{

    /**
     * Available filters
     *
     * @var array 
     */
    protected $filters = [
        NotificationStatusFilter::class,
        NotificationCategoryFilter::class,
    ];

    /**
     * items per page
     *
     * @var mixed 
     */
    public $perPage = 25;

    /**
     * @var string
     */
    protected $currentRecipientId = 'all';

    /**
     * @var int|null
     */
    protected $currentTypeId;

    /**
     * @return Builder
     */
    public function query()
    {
        $query = NotificationsModel::query()->with('contents');

//        if (request()->ajax()) {
//            $columns    = request()->get('columns', []);
//            $item       = array_first($columns, function ($item) {
//                return array_get($item, 'data') === 'recipient' && ! in_array(array_get($item, 'search.value'), ['', 'all'], true);
//            });
//
//            dd($item);
//
//            if ($item) {
//                $value = array_get($item, 'search.value');
//                $query->where('recipients', 'like', "%\"{$value}\"%");
//            }
//        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function ajax()
    {
        $request    = $this->datatables->request;
        $acl        = app('antares.acl')->make('antares/notifications');

        return $this->prepare()
            ->filter($this->setupSearchFilter($request))
            ->filterColumn('type', function($query, $keyword) {
                $query->where('type_id', $keyword);
            })
            ->editColumn('category', function (NotificationsModel $model) {
                return ucfirst($model->category);
            })
            ->editColumn('recipients', function (NotificationsModel $model) {
                return implode(', ', array_map('ucfirst', $model->recipients));
            })
            ->editColumn('type', function (NotificationsModel $model) {
                return $model->type->title;
            })
            ->editColumn('event', function (NotificationsModel $model) {
                return $model->event_label;
            })
            ->editColumn('active', function (NotificationsModel $model) {
                return ((int) $model->active) ?
                    '<span class="label-basic label-basic--success">' . trans('Yes') . '</span>' :
                    '<span class="label-basic label-basic--danger">' . trans('No') . '</span>';
            })
            ->addColumn('action', $this->getActionsColumn($acl))
            ->make(true);
    }

    /**
     * {@inheritdoc}
     */
    public function html()
    {
        $types = NotificationTypes::query()->pluck('title', 'id');
        $recipients = $this->getRecipients();

        return $this->setName('Notifications List')
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'Id', 'className' => 'w100'])
            ->addColumn(['data' => 'name', 'name' => 'name', 'title' => trans('antares/notifications::messages.title'), 'className' => 'text--bold'])
            ->addColumn(['data' => 'recipients', 'name' => 'recipients', 'title' => trans('antares/notifications::messages.notification_recipients')])
            ->addColumn(['data' => 'event', 'name' => 'event_model', 'title' => trans('antares/notifications::messages.notification_event')])
            ->addColumn(['data' => 'category', 'name' => 'category', 'title' => trans('antares/notifications::messages.notification_events_category')])
            ->addColumn(['data' => 'type', 'name' => 'type_id', 'title' => trans('antares/notifications::messages.notification_type')])
            ->addColumn(['data' => 'active', 'name' => 'active', 'title' => trans('antares/notifications::messages.notification_enabled')])
            ->addAction(['name' => 'edit', 'title' => '', 'class' => 'mass-actions dt-actions', 'orderable' => false, 'searchable' => false])
            ->addGroupSelect($recipients, 2, $this->currentRecipientId, ['data-prefix' => trans('antares/notifications::messages.datatables.select_recipient'), 'class' => 'mr24', 'id' => 'datatables-notification-recipient'])
            ->addGroupSelect($types, 5, $this->currentTypeId ?: $types->keys()->first(), ['data-prefix' => trans('antares/notifications::messages.datatables.select_type'), 'class' => 'mr24', 'id' => 'datatables-notification-type'])
            ->setDeferedData();
    }

    /**
     * @return array
     */
    protected function getRecipients() : array {
        /* @var $areaManager AreaManager */
        $areaManager = app()->make(AreaManager::class);

        $recipients = [
            'all' => 'All',
        ];

        foreach($areaManager->getAreas()->all() as $area) {
            $recipients[ $area->getId() ] = ucfirst($area->getId());
        }

        return $recipients;
    }

    /**
     * @param Request $request
     * @return Closure
     */
    protected function setupSearchFilter(Request $request) : Closure {
        return function(Builder $query) use($request) {
            $searchKeyword  = $request->keyword();
            $columns        = (array) $request->get('columns', []);

            array_walk($columns, function($item) {
                $itemData = Arr::get($item, 'data');

                if ($itemData === 'type') {
                    $this->currentTypeId = Arr::get($item, 'search.value');
                }

                if ($itemData === 'recipients') {
                    $this->currentRecipientId = Arr::get($item, 'search.value');
                }

                return false;
            });

//            if (!$this->currentTypeId) {
//                $this->currentTypeId = NotificationTypes::query()->where('name', 'mail')->first()->id;
//            }
//
//            $query->where('type_id', $this->currentTypeId);
//
//            if ($this->currentRecipientId !== 'all') {
//                $query->where('recipients', 'like', "%\"{$this->currentRecipientId}\"%");
//            }
//
            if ($searchKeyword) {
                $searchKeyword = '%' . $searchKeyword . '%';

                $query->where(function(Builder $query) use($searchKeyword) {
                    $query
                        ->whereHas('contents', function(Builder $query) use($searchKeyword) {
                            $query->where('title', 'LIKE', $searchKeyword);
                        });
                });
            }
        };
    }

    /**
     * @param Authorization $acl
     * @return Closure
     */
    protected function getActionsColumn(Authorization $acl)
    {
        return function (NotificationsModel $notification) use($acl) {
            $contextMenu = DataTableActionsHelper::make();

            if($acl->can('notifications-edit')) {
                $contextMenu->addAction(
                    handles('antares::notifications/' . $notification->id . '/edit'),
                    trans('Edit'), [
                        'data-icon' => 'edit',
                    ]
                );
            }

            if($acl->can('notifications-change-status')) {
                $contextMenu->addAction(
                    handles('antares::notifications/' . $notification->id . '/changeStatus'),
                    $notification->active ? trans('antares/notifications::messages.notification_disable') : trans('antares/notifications::messages.notification_enable'), [
                        'class'             => 'triggerable confirm',
                        'data-http-method'  => 'POST',
                        'data-icon'         => $notification->active ? 'minus-circle' : 'check-circle',
                        'data-title'        => trans('antares/notifications::messages.modals.general_prompt'),
                        'data-description'  => trans('antares/notifications::messages.modals.change_status', [
                            'id'    => $notification->id,
                            'name'  => $notification->name,
                        ]),
                    ]
                );
            }

            if($acl->can('notifications-test')) {
                $contextMenu->addAction(
                    handles('antares::notifications/' . $notification->id . '/sendTest'),
                    trans('antares/notifications::messages.notification_send_preview'), [
                        'class'             => 'triggerable confirm',
                        'data-http-method'  => 'POST',
                        'data-icon'         => 'desktop-windows',
                        'data-title'        => trans('antares/notifications::messages.modals.general_prompt'),
                        'data-description'  => trans('antares/notifications::messages.modals.send_preview', [
                            'id'    => $notification->id,
                            'name'  => $notification->name,
                        ]),
                    ]
                );
            }

            if ( $acl->can('notifications-delete') ) {
                $contextMenu->addDeleteAction(
                    handles('antares::notifications/' . $notification->id),
                    trans('antares/notifications::messages.notification_delete'), [
                        'data-title'        => trans('antares/notifications::messages.modals.general_prompt'),
                        'data-description'  => trans('antares/notifications::messages.modals.delete', [
                            'id'    => $notification->id,
                            'name'  => $notification->name,
                        ]),
                    ]
                );
            }

            return $contextMenu->build($notification->id);
        };
    }

}
