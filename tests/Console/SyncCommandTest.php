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


namespace Antares\Templates\Model\TestCase;

use Antares\Templates\Console\SyncCommand;
use Antares\Templates\Memory\JobsMemory;
use Antares\Memory\MemoryManager;
use Antares\Memory\Provider;
use Antares\Model\Component;
use Antares\Support\Collection;
use Antares\Testing\TestCase;
use Mockery as m;
use stdClass;
use Symfony\Component\Console\Style\OutputStyle;

class SyncCommandTest extends TestCase
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
     * Tests Antares\Templates\Console\SyncCommand::handle
     */
    public function testHandle()
    {
        $this->app[Component::class] = $componentMock               = m::mock(Component::class);
        $mockObject                  = new stdClass();
        $mockObject->name            = 'acl_antares';
        $mockObject->id              = 1;
        $collection                  = new Collection([
            $mockObject
        ]);
        $this->app[Provider::class]  = $mProvider                   = m::mock(Provider::class);
        $this->app['antares.memory'] = $memoryManagerMock           = m::mock(MemoryManager::class);
        $memoryManagerMock->shouldReceive('make')->with('component')->once()->andReturn($mProvider)
                ->shouldReceive('make')->with('jobs')->once()->andReturn($jobsMemory                  = m::mock(JobsMemory::class));


        $mProvider->shouldReceive('get')->with('extensions.active')->once()->andReturn([
            "components/templates" => [
                "path"        => "base::src/components/templates",
                "source-path" => "base::src/components/templates",
                "name"        => "templates",
                "full_name"   => "Templates Manager",
                "description" => "Templates Manager Antares",
                "author"      => "Łukasz Cirut",
                "url"         => "https://antares.com",
                "version"     => "0.5",
                "config"      => [],
                "autoload"    => [],
                "provides"    => [
                    "Antares\Templates\TemplatesServiceProvider",
                    "Antares\Templates\CommandServiceProvider",
                    "Antares\Templates\ScheduleServiceProvider"
                ],
            ]
        ]);

        $componentMock->shouldReceive('all')->withNoArgs()->andReturn($collection);

        $command    = new SyncCommand();
        $command->setOutput($outputMock = m::mock(OutputStyle::class));
        $info       = '<info>No jobs found.</info>';
        $outputMock->shouldReceive('writeln')->with($info, 32)->once()->andReturn($info);
        $this->assertNull($command->handle());
    }

    /**
     * Tests Antares\Templates\Console\SyncCommand::setOutput
     */
    public function testSetOutput()
    {
        $command    = new SyncCommand();
        $this->assertInstanceOf(SyncCommand::class, $command->setOutput($outputMock = m::mock(OutputStyle::class)));
    }

}
