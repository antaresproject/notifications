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

namespace Antares\Notifications\Processor;

use Antares\Notifications\Http\Datatables\Logs as Datatables;
use Antares\Notifications\Http\Presenters\Breadcrumb;
use Antares\Notifications\Contracts\LogsListener;
use Antares\Foundation\Processor\Processor;

class LogsProcessor extends Processor
{

    /**
     * breadcrumbs instance
     *
     * @var Breadcrumb
     */
    protected $breadcrumb;

    /**
     * Datatables instance
     *
     * @var Datatables
     */
    protected $datatables;

    /**
     * Construct
     * 
     * @param Breadcrumb $breadcrumb
     * @param Datatables $datatables
     */
    public function __construct(Breadcrumb $breadcrumb, Datatables $datatables)
    {
        $this->breadcrumb = $breadcrumb;
        $this->datatables = $datatables;
    }

    /**
     * Default index action
     * 
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $this->breadcrumb->onLogsList();
        return $this->datatables->render('antares/notifications::admin.logs.index');
    }

    /**
     * Preview notification log
     * 
     * @param mixed $id
     * @return \Illuminate\View\View
     */
    public function preview($id)
    {
        
    }

    /**
     * Deletes notification log
     * 
     * @param mixed $id
     * @param LogsListener $listener
     * @return \Illuminate\Http\RedirectResponse
     */
    public function delete($id, LogsListener $listener)
    {
        return $listener->deleteSuccess();
    }

}
