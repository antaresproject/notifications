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


namespace Antares\Templates\Http\Handlers\TestCase;

use Antares\Templates\Http\Handlers\TemplatesPane;
use Antares\Testbench\TestCase;
use Antares\Widget\Handlers\Menu;
use Antares\Widget\Handlers\Pane;
use Antares\Widget\WidgetManager;
use Illuminate\Contracts\View\Factory;
use Mockery as m;

class TemplatesPaneTest extends TestCase
{

    /**
     * Setup the test environment.
     */
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Teardown the test environment.
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Tests \Antares\Templates\Http\Handlers\TemplatesPane::getInstance
     */
    public function testGetInstance()
    {
        $this->assertInstanceOf(TemplatesPane::class, TemplatesPane::getInstance());
    }

    /**
     * Tests \Antares\Templates\Http\Handlers\TemplatesPane::compose
     */
    public function testCompose()
    {
        $stub                                 = TemplatesPane::getInstance();
        $this->app['antares.platform.memory'] = m::mock('Antares\Memory\Provider');
        $acl                                  = m::mock('Antares\Authorization\Factory')
                ->shouldReceive('make')->with("antares/templates")->andReturnSelf()
                ->shouldReceive("can")->with(m::type("String"))->andReturn(true)
                ->shouldReceive('attach')->with($this->app['antares.platform.memory'])->andReturnSelf()
                ->getMock();

        $this->app['antares.widget'] = $widgetMock                  = m::mock(WidgetManager::class);
        $widgetMock->shouldReceive('make')->with('pane.left')->andReturn($pane                        = m::mock(Pane::class));
        $pane->shouldReceive('add')->with('templates')->andReturnSelf()
                ->shouldReceive('content')->with('foo')->andReturnSelf();



        $widgetMock->shouldReceive('make')->with('menu.templates.pane')->andReturn($menuMock                 = m::mock(Menu::class));
        $menuMock->shouldReceive('add')->withAnyArgs()->andReturnSelf()
                ->shouldReceive('link')->withAnyArgs()->andReturnSelf()
                ->shouldReceive('title')->withAnyArgs()->andReturnSelf()
                ->shouldReceive('prepend')->withAnyArgs()->andReturnSelf();
        $this->app['antares.acl'] = $acl;

        $foundation               = m::mock('\Antares\Contracts\Foundation\Foundation');
        $foundation->shouldReceive('handles')->withAnyArgs()->andReturn('#url');
        $this->app['translator']  = $translator               = m::mock('\Illuminate\Translator\Translator');
        $translator->shouldReceive('trans')->withAnyArgs()->andReturn('foo');
        $this->app['antares.app'] = $foundation;
        $this->app['view']        = $view                     = m::mock(Factory::class);
        $view->shouldReceive('make')->with(m::type('String'))->andReturnSelf()
                ->shouldReceive('render')->withNoArgs()->andReturn('foo');

        $this->assertNull($stub->compose());
    }

}
