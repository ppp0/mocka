<?php

namespace MockaTests\Mocka;

use Mocka\ClassMock;
use Mocka\ClassTrait;
use MockaMocks\AbstractClass;

class ClassMockTest extends \PHPUnit_Framework_TestCase {

    public function testGenerateCode() {
        $parentClassName = '\\MockaMocks\\AbstractClass';

        $classMock = new ClassMock($parentClassName);
        $name = $classMock->getName();
        $namespace = $classMock->getNamespace();
        $expectedMockCode = <<<EOD
namespace $namespace;

class $name extends $parentClassName {

    use \\Mocka\\ClassTrait;

    public function foo() {
        return \$this->_callMethod(__FUNCTION__, func_get_args());
    }

    public function __construct(\$arg1 = null, \$arg2 = null) {
        return \$this->_callMethod(__FUNCTION__, func_get_args());
    }

    public function bar() {
        return \$this->_callMethod(__FUNCTION__, func_get_args());
    }

    public static function jar() {
        return static::_callStaticMethod(__FUNCTION__, func_get_args());
    }
}
EOD;
        $this->assertSame($expectedMockCode, $classMock->generateCode());
    }

    public function testMockMethod() {
        $parentClassName = '\\MockaMocks\\AbstractClass';
        $classMock = new ClassMock($parentClassName);
        /** @var ClassTrait|AbstractClass $object */
        $object = $classMock->newInstance();

        $this->assertSame('bar', $object->bar());

        $classMock->mockMethod('bar')->set(function () {
            return 'foo';
        });
        $this->assertSame('foo', $object->bar());
    }

    /**
     * @expectedException \Mocka\Exception
     */
    public function testMockMethodFinal() {
        $classMock = new ClassMock('\\MockaMocks\\AbstractClass');
        $classMock->mockMethod('zoo');
    }

    public function testMockStaticMethod() {
        $classMock = new ClassMock('\\MockaMocks\\AbstractClass');
        /** @var AbstractClass $className */
        $className = $classMock->getClassName();

        $this->assertSame('jar', $className::jar());
        $classMock->mockStaticMethod('jar')->set(function() {
            return 'foo';
        });
        $this->assertSame('foo', $className::jar());

        $classMock->mockStaticMethod('nonexistent')->set(function() {
            return 'bar';
        });
        $this->assertSame('bar', $className::nonexistent());
    }

    public function testNewInstanceConstructorArgs() {
        $classMock = new ClassMock('\\MockaMocks\\AbstractClass');
        $constructorArgs = ['foo', 'bar'];
        /** @var AbstractClass $object */
        $object = $classMock->newInstance($constructorArgs);
        $this->assertSame($object->constructorArgs, $constructorArgs);
    }
}
