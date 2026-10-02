<?php
declare(strict_types=1);

use Raxos\Reflection\{Attributable, ClassReflector};
use RaxosTests\Reflection\{DerivedMarker, MarkedImplementation, RepeatedMarkers, UnitChild, UnitMarker};

covers(Attributable::class);

it('distinguishes exact attributes from inherited attribute types', function (): void {
    $class = new ClassReflector(RepeatedMarkers::class);
    expect($class->hasAttribute(UnitMarker::class))->toBeTrue()
        ->and(array_column($class->getAttributes(UnitMarker::class), 'value'))->toBe(['one', 'two', 'derived'])
        ->and($class->getRawAttributes(UnitMarker::class))->toHaveCount(3)
        ->and($class->getRawAttribute(DerivedMarker::class)->getName())->toBe(DerivedMarker::class)
        ->and($class->getAttribute(DerivedMarker::class)->value)->toBe('derived')
        ->and($class->getAttribute(Deprecated::class))->toBeNull();
});

it('searches parent and interface attributes only when recursion is requested', function (): void {
    $child = new ClassReflector(UnitChild::class);
    expect($child->getAttribute(UnitMarker::class))->toBeNull()
        ->and($child->getAttribute(UnitMarker::class, true)->value)->toBe('parent')
        ->and(new ClassReflector(MarkedImplementation::class)->getAttribute(UnitMarker::class, true)->value)->toBe('interface')
        ->and(new ClassReflector(stdClass::class)->getAttribute(UnitMarker::class, true))->toBeNull();
});
