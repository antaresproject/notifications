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

namespace Antares\Notifications\Http\Datatables;

use Antares\Authorization\Authorization;
use Antares\Modules\BillevioBase\Helpers\DataTableActionsHelper;
use Antares\Notifications\Model\Notifications as NotificationsModel;
use Antares\Notifications\Model\NotificationCategory;
use Antares\Notifications\Filter\NotificationFilter;
use Antares\Notifications\Model\NotificationTypes;
use Antares\Datatables\Services\DataTable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Closure;

class NotificationsDataTable extends DataTable
{

    /**
     * Available filters
     *
     * @var array 
     */
    protected $filters = [
        NotificationFilter::class
    ];

    /**
     * items per page
     *
     * @var mixed 
     */
    public $perPage = 25;

    /**
     * @var int|null
     */
    protected $currentCategoryId;

    /**
     * @var int|null
     */
    protected $currentTypeId;

    /**
     * @return Builder
     */
    public function query()
    {
        return NotificationsModel::query()->with('category', 'contents');
    }

    /**
     * {@inheritdoc}
     */
    public function ajax()
    {
        $request         = request();
        $acl             = app('antares.acl')->make('antares/notifications');

        return $this->prepare()
            ->filter($this->setupSearchFilter($request))
            ->filterColumn('type', function($query, $keyword) {
                $query->where('type_id', $keyword);
            })
            ->filterColumn('category', function($query, $keyword) {
                $query->where('category_id', $keyword);
            })
            ->editColumn('category', function (NotificationsModel $model) {
                return $model->category->title;
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
        $categories = $this->categories();
        $types      = $this->types();

        return $this->setName('Notifications List')
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'Id'])
            ->addColumn(['data' => 'name', 'name' => 'name', 'title' => trans('antares/notifications::messages.title'), 'className' => 'bolded'])
            ->addColumn(['data' => 'event', 'name' => 'event_model', 'title' => trans('Event')])
            ->addColumn(['data' => 'category', 'name' => 'category_id', 'title' => trans('Category')])
            ->addColumn(['data' => 'type', 'name' => 'type_id', 'title' => trans('Type')])
            ->addColumn(['data' => 'active', 'name' => 'active', 'title' => trans('Enabled')])
            ->addAction(['name' => 'edit', 'title' => '', 'class' => 'mass-actions dt-actions', 'orderable' => false, 'searchable' => false])
            ->addGroupSelect($categories, 3, $this->currentCategoryId ?: $categories->keys()->first(), ['data-prefix' => trans('antares/notifications::messages.datatables.select_category'), 'class' => 'mr24', 'id' => 'datatables-notification-category'])
            ->addGroupSelect($types, 4, $this->currentTypeId ?: $types->keys()->first(), ['data-prefix' => trans('antares/notifications::messages.datatables.select_type'), 'class' => 'mr24', 'id' => 'datatables-notification-type'])
            ->setDeferedData();
    }

    /**
     * @param Request $request
     * @return Closure
     */
    protected function setupSearchFilter(Request $request) : Closure {
        return function(Builder $query) use($request) {
            $searchKeyword  = Arr::get($request->get('search'), 'value');
            $columns        = (array) $request->get('columns', []);

            array_walk($columns, function($item) use(&$typeId, &$categoryId) {
                $itemData = Arr::get($item, 'data');

                if ($itemData === 'type') {
                    $this->currentTypeId = Arr::get($item, 'search.value');
                }
                if ($itemData === 'category') {
                    $this->currentCategoryId = Arr::get($item, 'search.value');
                }

                return false;
            });

            if (!$this->currentCategoryId) {
                $this->currentCategoryId = NotificationCategory::query()->where('name', 'default')->first()->id;
            }

            $query->where('category_id', $this->currentCategoryId);

            if (!$this->currentTypeId) {
                $this->currentTypeId = NotificationTypes::query()->where('name', 'mail')->first()->id;
            }

            $query->where('type_id', $this->currentTypeId);

            if ($searchKeyword !== '') {
                $searchKeyword = '%' . $searchKeyword . '%';

                $query->where(function(Builder $query) use($searchKeyword) {
                    $query
                        ->whereHas('contents', function(Builder $query) use($searchKeyword) {
                            $query->where('title', 'LIKE', $searchKeyword);
                        })
                        ->orWhereHas('category', function(Builder $query) use($searchKeyword) {
                            $query->where('title', 'LIKE', $searchKeyword);
                        });
                });
            }
        };
    }

    /**
     * Creates select for categories
     * 
     * @return Collection
     */
    protected function categories(): Collection {
        return NotificationCategory::query()->pluck('title', 'id');
    }

    /**
     * Creates select for types
     * 
     * @return Collection
     */
    protected function types(): Collection {
        return NotificationTypes::query()->pluck('title', 'id');
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
                    $notification->active ? trans('Disable') : trans('Enable'), [
                        'class'             => 'triggerable confirm',
                        'data-http-method'  => 'POST',
                        'data-icon'         => $notification->active ? 'minus-circle' : 'check-circle',
                        'data-title'        => trans('Are you sure?'),
                        'data-description'  => trans('Changing status of notification') . ' #' . $notification->id,
                    ]
                );
            }

            if($acl->can('notifications-test')) {
                $contextMenu->addAction(
                    handles('antares::notifications/' . $notification->id . '/sendTest'),
                    trans('Send preview'), [
                        'class'             => 'triggerable confirm',
                        'data-http-method'  => 'POST',
                        'data-icon'         => 'desktop-windows',
                        'data-title'        => trans('Are you sure?'),
                        'data-description'  => trans('Sending preview notification with item') . $notification->lang( lang() )->title,
                    ]
                );
            }

            if ( $acl->can('notifications-delete') ) {
                $contextMenu->addDeleteAction(
                    handles('antares::notifications/' . $notification->id),
                    trans('Delete'), [
                        'data-title'        => trans('Are you sure?'),
                        'data-description'  => trans('Deleting Item') .' #' . $notification->id,
                    ]
                );
            }

            return $contextMenu->build($notification->id);
        };
    }

}
