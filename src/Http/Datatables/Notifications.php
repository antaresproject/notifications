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

use Antares\Notifications\Model\NotificationContents;
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

class Notifications extends DataTable
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
     * @return Builder
     */
    public function query()
    {
        return NotificationsModel::query()
            ->with('category', 'contents')
            ->whereHas('contents', function (Builder $query) {
                $query->where('lang_id', lang_id());
            });
    }

    /**
     * {@inheritdoc}
     */
    public function ajax()
    {
        $request         = request();
        $acl             = app('antares.acl')->make('antares/notifications');
        $canUpdate       = $acl->can('notifications-edit');
        $canTest         = $acl->can('notifications-test');
        $canChangeStatus = $acl->can('notifications-change-status');
        $canDelete       = $acl->can('notifications-delete');

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
            ->editColumn('classname', function (NotificationsModel $model) {
                return last(explode('\\', $model->classname));
            })
            ->editColumn('title', function (NotificationsModel $model) {
                $first = $model->contents->first();
                return ($first instanceof NotificationContents) ? $first->title : '';
            })
            ->editColumn('active', function (NotificationsModel $model) {
                return ((int) $model->active) ?
                    '<span class="label-basic label-basic--success">' . trans('Yes') . '</span>' :
                    '<span class="label-basic label-basic--danger">' . trans('No') . '</span>';
            })
            ->addColumn('action', $this->getActionsColumn($canUpdate, $canTest, $canChangeStatus, $canDelete))
            ->make(true);
    }

    /**
     * {@inheritdoc}
     */
    public function html()
    {
        publish('notifications', ['js/notifications-table.js']);

        $categories = $this->categories();
        $types      = $this->types();

        return $this->setName('Notifications List')
            ->addColumn(['data' => 'id', 'name' => 'id', 'title' => 'Id'])
            ->addColumn(['data' => 'title', 'name' => 'title', 'title' => trans('antares/notifications::messages.title'), 'className' => 'bolded'])
            ->addColumn(['data' => 'classname', 'name' => 'classname', 'title' => trans('Event')])
            ->addColumn(['data' => 'category', 'name' => 'category_id', 'title' => trans('Category')])
            ->addColumn(['data' => 'type', 'name' => 'type_id', 'title' => trans('Type')])
            ->addColumn(['data' => 'active', 'name' => 'active', 'title' => trans('Enabled')])
            ->addAction(['name' => 'edit', 'title' => '', 'class' => 'mass-actions dt-actions', 'orderable' => false, 'searchable' => false])
            ->addGroupSelect($categories, 3, $categories->keys()->first(), ['data-prefix' => trans('antares/notifications::messages.datatables.select_category'), 'class' => 'mr24', 'id' => 'datatables-notification-category'])
            ->addGroupSelect($types, 4, $types->keys()->first(), ['data-prefix' => trans('antares/notifications::messages.datatables.select_type'), 'class' => 'mr24', 'id' => 'datatables-notification-type'])
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
            $typeId         = null;
            $categoryId     = null;

            array_walk($columns, function($item) use(&$typeId, &$categoryId) {
                $itemData = Arr::get($item, 'data');

                if ($itemData === 'type') {
                    $typeId = Arr::get($item, 'search.value');
                }
                if ($itemData === 'category') {
                    $categoryId = Arr::get($item, 'search.value');
                }

                return false;
            });

            if (!$categoryId) {
                $categoryId = NotificationCategory::query()->where('name', 'default')->first()->id;
            }

            $query->where('category_id', $categoryId);

            if (!$typeId) {
                $typeId = NotificationTypes::query()->where('name', 'email')->first()->id;
            }

            $query->where('type_id', $typeId);

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
    protected function categories(): Collection
    {
        return NotificationCategory::query()->pluck('title', 'id');
    }

    /**
     * Creates select for types
     * 
     * @return Collection
     */
    protected function types(): Collection
    {
        return NotificationTypes::query()->pluck('title', 'id');
    }

    /**
     * Get actions column for table builder.
     * @return callable
     */
    protected function getActionsColumn($canUpdate, $canTest, $canChangeStatus, $canDelete)
    {
        return function (NotificationsModel $row) use($canUpdate, $canTest, $canChangeStatus, $canDelete) {
            $btns = [];
            $html = app('html');
            if ($canUpdate) {
                $btns[] = $html->create('li', $html->link(handles("antares::notifications/edit/" . $row->id), trans('Edit'), ['data-icon' => 'edit']));
            }

            if ($canChangeStatus) {
                $btns[] = $html->create('li', $html->link(handles("antares::notifications/changeStatus/" . $row->id), $row->active ? trans('Disable') : trans('Enable'), ['class' => "triggerable confirm", 'data-icon' => $row->active ? 'minus-circle' : 'check-circle', 'data-title' => trans("Are you sure?"), 'data-description' => trans('Changing status of notification') . ' #' . $row->contents[0]->title]));
            }
            if ($canTest && in_array($row->type->name, ['email', 'sms'])) {
                $btns[] = $html->create('li', $html->link(handles("antares::notifications/sendtest/" . $row->id), trans('Send preview'), ['class' => "triggerable confirm", 'data-icon' => 'desktop-windows', 'data-title' => trans("Are you sure?"), 'data-description' => trans('Sending preview notification with item') . ' #' . $row->contents[0]->title]));
            }

            if ($canDelete and ( ( $row->event == config('antares/notifications::default.custom_event')))) {
                $btns[] = $html->create('li', $html->link(handles("antares::notifications/delete/" . $row->id), trans('Delete'), ['class' => "triggerable confirm", 'data-icon' => 'delete', 'data-title' => trans("Are you sure?"), 'data-description' => trans('Deleting item #') . ' #' . $row->id]));
            }
            if (empty($btns)) {
                return '';
            }

            $section = $html->create('div', $html->create('section', $html->create('ul', $html->raw(implode('', $btns)))), ['class' => 'mass-actions-menu'])->get();

            return '<i class="zmdi zmdi-more"></i>' . $html->raw($section)->get();
        };
    }

}
