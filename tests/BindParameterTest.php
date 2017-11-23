<?php

namespace Antares\Notifications\TestCase;

use Antares\Notifications\BindParameter;
use PHPUnit\Framework\TestCase;

class BindParameterTest extends TestCase
{

    /**
     * @var BindParameter
     */
    protected $parameter;

    public function setUp()
    {
        parent::setUp();

        $this->parameter = new BindParameter('test', TestParameterObject::class);
    }

    public function testCreation() {
        $this->assertEquals('test', $this->parameter->getVariableName());
        $this->assertEquals(TestParameterObject::class, $this->parameter->getClassName());
    }

    public function testWringClassName() {
        $this->expectException(\InvalidArgumentException::class);

        new BindParameter('test', 'InvalidClassName');
    }

    public function testMatchingToValue() {
        $this->assertFalse( $this->parameter->isMatchToValue('dump_value') );
        $this->assertFalse( $this->parameter->isMatchToValue(new InvalidTestParameterObject()) );
        $this->assertTrue( $this->parameter->isMatchToValue(new TestParameterObject()) );
    }

    public function testMatchingInArray() {
        $this->assertFalse( $this->parameter->isMatchIn(['foo' => 'wrong']) );
        $this->assertTrue( $this->parameter->isMatchIn(['test' => 'good']) );
    }

    public function testMatchingToParameter() {
        $class      = new \ReflectionClass(ParameterHolderObject::class);
        $parameters = $class->getConstructor()->getParameters();

        $this->assertTrue( $this->parameter->isMatchToParameter($parameters[0]) );
        $this->assertFalse( $this->parameter->isMatchToParameter($parameters[1]) );
        $this->assertFalse( $this->parameter->isMatchToParameter($parameters[2]) );
    }

}

class TestParameterObject {}

class InvalidTestParameterObject {}

class ParameterHolderObject {

    public function __construct(
        TestParameterObject $test,
        TestParameterObject $wrongName,
        InvalidTestParameterObject $wrong
    ) {

    }
}