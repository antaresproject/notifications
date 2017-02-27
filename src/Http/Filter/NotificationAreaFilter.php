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

namespace Antares\Notifications\Filter;

use Yajra\Datatables\Contracts\DataTableScopeContract;
use Antares\Datatables\Filter\SelectFilter;

class NotificationAreaFilter extends SelectFilter implements DataTableScopeContract
{

    /**
     * Name of filter
     *
     * @var String 
     */
    protected $name = 'User Area';

    /**
     * Column to search
     *
     * @var String
     */
    protected $column = 'notification_lang';

    /**
     * filter pattern
     *
     * @var String
     */
    protected $pattern = '%value';

    /**
     * Filter instance dataprovider
     * 
     * @return Collection
     */
    protected function options()
    {
//        $jobs   = Jobs::all(['id', 'name', 'value']);
//        $return = [];
//        foreach ($jobs as $job) {
//            $return[$job->id] = $job->value['title'];
//        }
//        return $return;
        return [];
    }

    /**
     * filters data by parameters from memory
     * 
     * @param mixed $builder
     */
    public function apply($builder)
    {
//        $params = $this->getParams();
//
//        if (is_null($ids = array_get($params, __CLASS__ . '.value'))) {
//            return false;
//        }
//        if (!empty($ids)) {
//            $builder->whereIn('job_id', $ids);
//        }
    }

}
