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

namespace Antares\Notifications\Http\Controllers\Admin\TestCase;

use Antares\Area\Facade\AreasManager;
use Antares\Model\User;
use Antares\Notifications\Model\Notifications;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Antares\Testing\TestCase;

class IndexControllerTest extends TestCase
{

    use DatabaseTransactions;

    /**
     * @var User
     */
    protected $admin;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->afterApplicationCreated(function() {
            AreasManager::manager()->setCurrentArea('admin');
        });

        parent::setUp();

        $this->admin = User::administrators()->first();
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::index
     */
    public function testIndex()
    {
        $this->get('notifications')->assertStatus(302);
        $this->actingAs($this->admin)->get('notifications')->assertStatus(200);
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::create
     */
    public function testCreate()
    {
        /* @var $r \Illuminate\Routing\Router */
        //$r = $this->app->make(\Illuminate\Routing\Router::class);

        //dd( $r->getRoutes()->get('GET') );

        $this->get('notifications/create')->assertStatus(302);
        $this->actingAs($this->admin)->get('notifications/create')->assertStatus(200);
    }

    /**
     * Tests Antares\Notifications\Http\Controllers\Admin\IndexController::edit
     */
    public function testEdit()
    {
        $model  = Notifications::query()->firstOrFail();
        $uri    = 'notifications/' . $model->id . '/edit';

        $this->get($uri)->assertStatus(302);
        $this->actingAs($this->admin)->get($uri)->assertStatus(200);
    }

}