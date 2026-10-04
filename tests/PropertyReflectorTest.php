<?php
declare(strict_types=1);

use Raxos\Reflection\ClassReflector;
use RaxosTests\Reflection\{UnitChild, UnitMarker};

covers(Raxos\Reflection\PropertyReflector::class);

it('gets, sets and unsets initialized properties and reports uninitialized values', function (): void {
    $instance = new UnitChild();
    $class = new ClassReflector($instance);
    $property = $class->getProperty('uninitialized');
    expect($property->isInitialized($instance))->toBeFalse()->and($property->getValue($instance, 42))->toBe(42)
        ->and(fn() => $property->getValue($instance))->toThrow(Error::class)
        ->and($property->accepts(3))->toBeTrue()->and($property->accepts('3'))->toBeFalse();
    $property->setValue($instance, 3);
    expect($property->getValue($instance))->toBe(3);
    $property->unsetValue($instance);
    expect($property->isInitialized($instance))->toBeFalse();
});

it('exposes visibility, nullability, attributes and promoted constructor defaults', function (): void {
    $class = new ClassReflector(UnitChild::class);
    $nullable = $class->getProperty('nullable');
    $promoted = $class->getProperty('promoted');
    expect($nullable->isNullable())->toBeTrue()->and($nullable->hasDefaultValue())->toBeTrue()->and($nullable->getDefaultValue())->toBeNull()
        ->and($nullable->getAttribute(UnitMarker::class)->value)->toBe('property')
        ->and($nullable->getClass()->getName())->toBe(UnitChild::class)->and($nullable->isPublic())->toBeTrue()
        ->and($class->getProperty('private')->isPrivate())->toBeTrue()->and($class->getProperty('protected')->isProtected())->toBeTrue()
        ->and($class->getProperty('readonly')->isReadonly())->toBeTrue()->and($nullable->isVirtual())->toBeFalse()
        ->and($nullable->isIterable())->toBeFalse()->and($nullable->hasType())->toBeTrue()->and($nullable->getIterableType())->toBeNull()
        ->and($promoted->isPromoted())->toBeTrue()->and($promoted->hasDefaultValue())->toBeTrue()->and($promoted->getDefaultValue())->toBe(4);
});
