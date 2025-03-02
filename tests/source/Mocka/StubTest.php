<?php

namespace MockaTests;

use Mocka\Invokables\Invokable\Stub;
use Mocka\Mocka;
use PHPUnit\Framework\TestCase;
use TypeError;

class StubTest extends TestCase {

    public function testIntegrated() {
        $method = new Stub();
        $method->set(function () {
            return 'foo';
        });
        $method->at(1, function () {
            return 'bar';
        });
        $method->at([2, 5], function () {
            return 'zoo';
        });

        $this->assertSame('foo', $method->invoke('context', []));
        $this->assertSame('bar', $method->invoke('context', []));
        $this->assertSame('zoo', $method->invoke('context', []));
        $this->assertSame('foo', $method->invoke('context', []));

        $method->set(function () {
            return 'def';
        });
        $this->assertSame('def', $method->invoke('context', []));
        $this->assertSame('zoo', $method->invoke('context', []));
    }

    public function testAssertingArguments() {
        $method = new Stub();
        $method->set(function($foo) {
            $this->assertSame('bar', $foo);
        });
        $method->invoke('context', ['bar']);
    }

    public function testReturnTypes() {
        $method = new Stub();
        $method->set(function($foo): string {
            return (string) $foo;
        });
        $this->assertSame('bar', $method->invoke('context', ['bar']));
    }

    public function testReturnTypesInvalid() {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Return value must be of type int, string returned');
        $method = new Stub();
        $method->set(function($foo): int {
            return (string) $foo;
        });
        $method->invoke('context', ['bar']);
    }

    public function testTypeHinting() {
        $this->expectException(TypeError::class);
        $this->expectExceptionMessage('Argument #1 ($mocka) must be of type Mocka\Mocka, string given');
        $method = new Stub();
        $method->set(function (Mocka $mocka) {
        });
        $method->invoke('context',['Invalid arg']);
    }

    public function testGetCallCount() {
        $method = new Stub();
        $this->assertSame(0, $method->getCallCount());
        $method->invoke('context', []);
        $this->assertSame(1, $method->getCallCount());
    }

    public function testAllInvocationsAreCounted() {
        $method = new Stub();
        $method->set(function () {
            throw new \Exception();
        });
        $method->at(1, function () {
            return 'bar';
        });

        try {
            $method->invoke('context', []);
            $this->fail('Did not throw an exception');
        } catch (\Exception $e) {
            $this->assertEquals(new \Exception(), $e);
        }
        $this->assertSame('bar', $method->invoke('context', []));
    }
}
