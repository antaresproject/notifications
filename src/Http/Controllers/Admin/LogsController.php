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

use Antares\Notifications\Http\Datatables\LogsDataTable;
use Antares\Notifications\Processor\LogsProcessor as Processor;
use Antares\Foundation\Http\Controllers\AdminController;
use Antares\UI\Navigation\Breadcrumbs\Manager;
use Illuminate\Http\Request;

class LogsController extends AdminController
{

    /**
     * LogsController constructor.
     * @param Manager $manager
     * @param Processor $processor
     */
    public function __construct(Manager $manager, Processor $processor)
    {
        parent::__construct();
        $this->processor = $processor;

        $manager->enabled(true);
    }

    /**
     * route acl access controlling
     */
    public function setupMiddleware()
    {
        $this->middleware("antares.can:antares/notifications::notifications-list", ['only' => ['index']]);
    }

    /**
     * @param LogsDataTable $dataTable
     * @return array
     */
    public function index(LogsDataTable $dataTable) {
        return $dataTable->render('antares/notifications::admin.logs.index');
    }

    /**
     * {@inheritdoc}
     */
    public function preview($id)
    {
        return $this->processor->preview($id);
    }

    /**
     * @param Request $request
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function delete(Request $request, $id = null) {
        $ids = $request->get('attr', []);

        if($id) {
            $ids[] = $id;
        }

        return $this->processor->delete(array_unique($ids))->notify()->resolve($request);
    }

}
