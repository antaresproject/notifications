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

namespace Antares\Notifications\Filter;

use Yajra\Datatables\Contracts\DataTableScopeContract;
use Antares\Notifications\Model\Notifications;
use Antares\Datatables\Filter\SelectFilter;

class NotificationNameFilter extends SelectFilter implements DataTableScopeContract
{

    /**
     * Name of filter
     *
     * @var String 
     */
    protected $name = 'Notification Name';

    /**
     * Column to search
     *
     * @var String
     */
    protected $column = 'notification_name';

    /**
     * filter pattern
     *
     * @var String
     */
    protected $pattern = 'antares/notifications::logs.filter.names';

    /**
     * Filter instance dataprovider
     * 
     * @return array
     */
    protected function options()
    {
        return Notifications::query()->pluck('event', 'id')->toArray();
    }

    /**
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $builder
     * @return void
     */
    public function apply($builder)
    {
        $values = $this->getValues();

        if (empty($values)) {
            return;
        }
        $builder->whereIn('tbl_notifications.id', $values);
    }

}
