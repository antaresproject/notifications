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


namespace Antares\Templates\Http\Controllers\Admin\TestCase;

use Antares\Templates\TemplatesServiceProvider;
use Antares\Templates\Http\Presenters\IndexPresenter;
use Antares\Templates\Processor\IndexProcessor;
use Antares\Testing\TestCase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\View\View;
use Mockery as m;

class IndexControllerTest extends TestCase
{

    use WithoutMiddleware;

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        $this->addProvider(TemplatesServiceProvider::class);
        parent::setUp();
        $this->disableMiddlewareForAllTests();
    }

    /**
     * Get processor mock.
     *
     * @return \Antares\Foundation\Processor\Account\ProfileDashboard
     */
    protected function getProcessorMock()
    {
        $kernel    = m::mock(\Illuminate\Contracts\Console\Kernel::class);
        $processor = m::mock(IndexProcessor::class, [m::mock(IndexPresenter::class), $kernel]);
        $processor->shouldReceive('update')->withAnyArgs()->andReturnNull();
        $this->app->instance(IndexProcessor::class, $processor);
        return $processor;
    }

    /**
     * Tests Antares\Templates\Http\Controllers\Admin\IndexController::index
     */
    public function testIndex()
    {
        $this->getProcessorMock()->shouldReceive('index')->once()->andReturn(View::class);
        $this->call('GET', 'admin/templates');
        $this->assertResponseOk();
    }

    /**
     * Tests Antares\Templates\Http\Controllers\Admin\IndexController::show
     */
    public function testShow()
    {
        $this->getProcessorMock()->shouldReceive('show')->once()->andReturn(View::class);
        $this->call('GET', 'admin/templates/show/1');
        $this->assertResponseOk();
    }

    /**
     * Tests Antares\Templates\Http\Controllers\Admin\IndexController::showFailed
     */
    public function testShowFailed()
    {
        $this->getProcessorMock()->shouldReceive('show')->once()
                ->andReturnUsing(function ($request, $listener) {
                    return $listener->showFailed();
                });
        $this->call('GET', 'admin/templates/show/1');
        $this->assertResponseStatus(302);
    }

    /**
     * Tests Antares\Templates\Http\Controllers\Admin\IndexController::edit
     */
    public function testEdit()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturn(View::class);
        $this->call('GET', 'admin/templates/edit/1');
        $this->assertResponseOk();
    }

    /**
     * Tests Antares\Templates\Http\Controllers\Admin\IndexController::update
     */
    public function testUpdate()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturnUsing(function ($id, $listener) {
            return $listener->updateSuccess();
        });
        $this->call('POST', 'admin/templates/update');
        $this->assertResponseStatus(200);
    }

    /**
     * Tests Antares\Templates\Http\Controllers\Admin\IndexController::updateFailed
     */
    public function testUpdateFailed()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturnUsing(function ($id, $listener) {
            return $listener->updateFailed();
        });
        $this->call('POST', 'admin/templates/update');
        $this->assertResponseStatus(200);
    }

    /**
     * Tests Antares\Templates\Http\Controllers\Admin\IndexController::updateSuccess
     */
    public function testUpdateSuccess()
    {
        $this->getProcessorMock()->shouldReceive('edit')->once()->andReturnUsing(function ($id, $listener) {
            return $listener->updateSuccess();
        });
        $this->call('POST', 'admin/templates/update');
        $this->assertResponseStatus(200);
    }

}
