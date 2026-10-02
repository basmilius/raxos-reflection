<?php
declare(strict_types=1);

use Raxos\Reflection\MethodReflector;
use RaxosTests\Reflection\UnitChild;

covers(MethodReflector::class);

it('invokes methods and exposes their parameter and return types', function (): void {
    $method = new MethodReflector(new ReflectionMethod(UnitChild::class, 'method'));
    expect($method->invokeArgs(new UnitChild(), [7, 'unit', 'a', 'b']))->toBe([7, 'unit', ['a', 'b']])
        ->and($method->getName())->toBe('method')->and($method->getClass()->getName())->toBe(UnitChild::class)
        ->and($method->getReturnType()->getName())->toBe('array')
        ->and($method->getParameter('required')->getType()->getName())->toBe('int')
        ->and($method->getParameter('missing'))->toBeNull()
        ->and($method->getShortName())->toBe('UnitChild::method(int $required, string $nullable, string $values)')
        ->and(new MethodReflector(new ReflectionMethod(UnitChild::class, 'noReturnType'))->getReturnType())->toBeNull();
});

it('restores the declaring class and method after serialization', function (): void {
    $method = new MethodReflector(new ReflectionMethod(UnitChild::class, 'count'));
    $copy = unserialize(serialize($method));
    expect($copy->getName())->toBe('count')->and($copy->getClass()->getName())->toBe(UnitChild::class)
        ->and($copy->invokeArgs(new UnitChild()))->toBe(3);
});
