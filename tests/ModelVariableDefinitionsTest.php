<?php

namespace Antares\Templates\TestCase;

use Antares\Notifications\BindParameter;
use Antares\Notifications\ModelVariableDefinitions;
use PHPUnit\Framework\TestCase;

class ModelVariableDefinitionsTest extends TestCase
{

    /**
     * @var BindParameter
     */
    protected $parameter;

    /**
     * @var \Closure
     */
    protected $default;

    public function setUp()
    {
        parent::setUp();

        $this->parameter = new BindParameter('tests', TestParameterObject::class);

        $this->default = function() {
            return new TestParameterObject;
        };
    }

    public function testCreation() {
        $model = new ModelVariableDefinitions($this->parameter, $this->default);

        $this->assertEquals($this->parameter, $model->getBindParameter());
        $this->assertEmpty($model->getAttributes());
        $this->assertEmpty($model->getPlaceholders());
        $this->assertEmpty($model->toVariables());
    }

    public function testSingleAddAttributes() {
        $model = new ModelVariableDefinitions($this->parameter, $this->default);

        $model->addAttribute('a', 'AAA');
        $model->addAttribute('b', 'BBB');

        $this->assertCount(2, $model->getAttributes());
        $this->assertCount(2, $model->getPlaceholders());
        $this->assertCount(2, $model->toVariables());
    }

    public function testMassAttributes() {
        $model = new ModelVariableDefinitions($this->parameter, $this->default);

        $data = [
            'a' => 'AAA',
            'b' => 'BBB',
        ];

        $model->setAttributes($data);

        $this->assertEquals($data, $model->getAttributes());
        $this->assertCount(2, $model->getAttributes());
        $this->assertCount(2, $model->getPlaceholders());
        $this->assertCount(2, $model->toVariables());
    }

    public function testAttributeDetails() {
        $model = new ModelVariableDefinitions($this->parameter, $this->default);

        $model->addAttribute('a', 'AAA');

        $variable = $model->toVariables()[0];

        $this->assertEquals(['tests.a'], $model->getPlaceholders());
        $this->assertEquals('tests.a', $variable->getCode());
        $this->assertEquals('Test AAA', $variable->getLabel());
        $this->assertEquals($this->parameter, $variable->getRequiredParameter());
        $this->assertFalse($variable->isCompiled());
    }

    public function testDefaultValueWithException() {
        $this->expectException(\DomainException::class);

        $default = function() {
            return 'wrong_value';
        };

        $model = new ModelVariableDefinitions($this->parameter, $default);
        $model->getDefault();
    }

    public function testDefaultValue() {
        $model = new ModelVariableDefinitions($this->parameter, $this->default);

        $this->assertInstanceOf(TestParameterObject::class, $model->getDefault());
    }

    public function testSatisfiedRequirements() {
        $model = new ModelVariableDefinitions($this->parameter, $this->default);

        $this->assertTrue($model->isSatisfiedRequirements(['tests' => 'good']));
        $this->assertFalse($model->isSatisfiedRequirements(['foo' => 'wrong']));
    }

}

class TestParameterObject {}