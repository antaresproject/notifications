<?php

namespace Antares\Templates\TestCase;

use Antares\Notifications\BindParameter;
use Antares\Notifications\Variable;
use PHPUnit\Framework\TestCase;

class VariableTest extends TestCase
{

    public function testCreationWithPlainValue() {
        $variable = new Variable('test_code', 'Test Code', 'test_value');

        $this->assertEquals('test_code', $variable->getCode());
        $this->assertEquals('Test Code', $variable->getLabel());
        $this->assertEquals('test_value', $variable->getValue());
        $this->assertNull($variable->getRequiredParameter());
        $this->assertTrue($variable->isCompiled());
    }

    public function testCreationWithClosureValue() {
        $variable = new Variable('test_code', 'Test Code', function() {
            return 'closure_value';
        });

        $this->assertEquals('closure_value', $variable->getValue());
        $this->assertFalse($variable->isSimpleType());
    }

    public function testSimpleType() {
        $this->assertTrue( (new Variable('test_code', 'Test Code', 123))->isSimpleType() );
        $this->assertTrue( (new Variable('test_code', 'Test Code', 123.45))->isSimpleType() );
        $this->assertTrue( (new Variable('test_code', 'Test Code', 'some_value'))->isSimpleType() );
    }

    public function testCreationException() {
        $this->expectException(\InvalidArgumentException::class);

        new Variable('test_code', 'Test Code', new \stdClass());
    }

    public function testCompilationFlag() {
        $variable = new Variable('test_code', 'Test Code', 'test_value');

        $variable->setAsCompiled(false);
        $this->assertFalse($variable->isCompiled());

        $variable->setAsCompiled(true);
        $this->assertTrue($variable->isCompiled());
    }

    public function testRequiredParameter() {
        $variable = new Variable('test_code', 'Test Code', 'test_value');
        $parameter = new BindParameter('aaa', 'Aaa');

        $variable->setRequiredParameter($parameter);
        $this->assertEquals($parameter, $variable->getRequiredParameter());
    }

}