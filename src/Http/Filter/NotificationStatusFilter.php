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

namespace Antares\Notifications\Filter;

use Yajra\Datatables\Contracts\DataTableScopeContract;
use Antares\Datatables\Filter\SelectFilter;

class NotificationStatusFilter extends SelectFilter implements DataTableScopeContract
{

    /**
     * name of filter
     *
     * @var String 
     */
    protected $name = 'Status';

    /**
     * column to search
     *
     * @var String
     */
    protected $column = 'status';

    /**
     * filter pattern
     *
     * @var String
     */
    protected $pattern = 'Status: %value';

    /**
     * filter instance dataprovider
     * 
     * @return array
     */
    protected function options()
    {
        return [
            0 => 'Disabled',
            1 => 'Enabled'
        ];
    }

    /**
     * Filters data by parameters from memory
     *
     * @param \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Query\Builder $builder
     * @return void
     */
    public function apply($builder)
    {
        $values = $this->getValues();

        if (empty($values)) {
            return;
        }

        if( ! is_array($values) ) {
            $values = [$values];
        }

        $builder->whereIn('active', $values);
    }

}
