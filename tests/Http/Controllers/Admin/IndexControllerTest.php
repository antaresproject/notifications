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

namespace Antares\Notifications\Http\Controllers\Admin\TestCase;

use Antares\Area\Facade\AreasManager;
use Antares\Model\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Antares\Testing\TestCase;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Illuminate\View\View;
use Mockery as m;

class IndexControllerTest extends TestCase
{

    use WithoutMiddleware;
    use DatabaseTransactions;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->afterApplicationCreated(function() {
            AreasManager::manager()->setCurrentArea('admin');
        });

        parent::setUp();

        $this->disableMiddlewareForAllTests();
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::index
     */
    public function testIndex()
    {
        /* @var $user User */
        $user = factory(User::class)->create();
        //$user->attachRole('administrator');

        /* @var $r Router */
        $r = $this->app->make(Router::class);

        dd($r->getRoutes());

        $this->actingAs($user)->get('/admin/notifications')->assertStatus(202);
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::edit
     */
    public function testEdit()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturn(View::class);
        $this->call('GET', 'antares/notifications/edit/1');
        $this->assertResponseOk();
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::update
     */
    public function testUpdate()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturnUsing(function ($id, $listener) {
            return $listener->updateSuccess();
        });
        $this->call('POST', 'antares/notifications/update');
        $this->assertResponseStatus(200);
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::updateFailed
     */
    public function testUpdateFailed()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturnUsing(function ($id, $listener) {
            return $listener->updateFailed();
        });
        $this->call('POST', 'antares/notifications/update');
        $this->assertResponseStatus(200);
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::updateSuccess
     */
    public function testUpdateSuccess()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturnUsing(function ($id, $listener) {
            return $listener->updateSuccess();
        });
        $this->call('POST', 'antares/notifications/update');
        $this->assertResponseStatus(200);
    }

}
