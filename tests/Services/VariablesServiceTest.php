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

namespace Antares\Notifications\Services\TestCase;

use Antares\Notifications\Contracts\ModelVariablesResoluble;
use Antares\Notifications\Services\ModuleVariables;
use Antares\Notifications\Services\VariablesService;
use Antares\Notifications\Variable;
use Antares\Testing\TestCase;
use Illuminate\Contracts\Events\Dispatcher;
use Mockery as m;

class VariablesServiceTest extends TestCase
{

    /**
     * @var VariablesService
     */
    protected $variablesService;

    public function setUp() {
        parent::setUp();

        $this->variablesService = new VariablesService($this->app->make(Dispatcher::class));
    }

    public function tearDown() {
        parent::tearDown();

        m::close();
    }

    public function testEmptyVariables() {
        $this->assertEmpty($this->variablesService->getNamedVariables());
        $this->assertEmpty($this->variablesService->all());
    }

    public function testRegisterWithModel() {
        $model = m::mock(ModelVariablesResoluble::class)
            ->shouldReceive('applyVariables')
            ->getMock();

        $registeredModel = $this->variablesService->register('module_name', $model);

        $this->assertInstanceOf(ModuleVariables::class, $registeredModel);
        $this->assertEquals('module_name', $registeredModel->getModuleName());
    }

    public function testNamedVariables() {
        $this->variablesService->register('unit_test')
            ->set('a', 'A Value', 'a_value')
            ->set('b', 'B Value', 'b_value');

        $variables = $this->variablesService->getNamedVariables();

        $expected = [
            'unit_test::a',
            'unit_test::b',
        ];

        $this->assertEquals($expected, array_keys($variables));

        foreach($variables as $object) {
            $this->assertInstanceOf(Variable::class, $object);
        }
    }

    public function testAllVariables() {
        $this->variablesService->register('unit_test')
            ->set('a', 'A Value', 'a_value')
            ->set('b', 'B Value', 'b_value');

        $variables = $this->variablesService->all();

        foreach($variables as $object) {
            $this->assertInstanceOf(ModuleVariables::class, $object);
        }
    }

    public function testFindByModule() {
        $this->variablesService->register('unit_test')
            ->set('a', 'A Value', 'a_value')
            ->set('b', 'B Value', 'b_value');

        $this->assertNull($this->variablesService->findByModule('not_exists_module'));
        $this->assertInstanceOf(ModuleVariables::class, $this->variablesService->findByModule('unit_test'));
    }

    public function testFindByCodeWithException() {
        $this->expectException(\InvalidArgumentException::class);

        $this->variablesService->findByCode('code_without_module_name');
    }

    public function testFindByCode() {
        $this->variablesService->register('unit_test')
            ->set('a', 'A Value', 'a_value')
            ->set('b', 'B Value', 'b_value');

        $this->assertInstanceOf(Variable::class, $this->variablesService->findByCode('unit_test::a'));
        $this->assertInstanceOf(Variable::class, $this->variablesService->findByCode('unit_test::b'));

        $this->assertNull($this->variablesService->findByCode('non_module::a'));
        $this->assertNull($this->variablesService->findByCode('unit_test::c'));
    }

}
