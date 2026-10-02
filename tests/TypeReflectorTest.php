<?php
declare(strict_types=1);

use Raxos\Reflection\TypeReflector;

covers(TypeReflector::class);

it('accepts PHP literal and mixed types', function (string $type, mixed $value, bool $accepted): void {
    expect((new TypeReflector($type))->accepts($value))->toBe($accepted);
})->with([
    ['mixed', 123, true], ['mixed', null, true], ['true', true, true], ['true', false, false],
    ['false', false, true], ['false', true, false], ['null', null, true], ['null', 0, false],
    ['never', 1, false], ['void', null, false], ['int|string', 1, true], ['int|string', [], false],
    ['?string', null, true], ['?string', 'text', true], ['?string', 1, false],
]);

it('accepts interface instances and intersections', function (): void {
    $iterator = new ArrayIterator([1]);
    expect((new TypeReflector(Countable::class))->accepts($iterator))->toBeTrue();
    expect((new TypeReflector(Countable::class))->accepts(new stdClass()))->toBeFalse();
    expect((new TypeReflector('Countable&Iterator'))->accepts($iterator))->toBeTrue();
    expect((new TypeReflector('iterable'))->accepts([1]))->toBeTrue();
    expect((new TypeReflector('Iterator'))->accepts([1]))->toBeFalse();
});

it('classifies scalar, iterable, stringable and enum definitions', function (): void {
    expect(new TypeReflector('int')->isBuiltIn())->toBeTrue()->and(new TypeReflector('int')->isScalar())->toBeTrue()
        ->and(new TypeReflector('array')->isScalar())->toBeFalse()->and(new TypeReflector('array')->isIterable())->toBeTrue()
        ->and(new TypeReflector(ArrayIterator::class)->isIterable())->toBeTrue()->and(new TypeReflector(Generator::class)->isIterable())->toBeTrue()
        ->and(new TypeReflector('string')->isStringable())->toBeTrue()->and(new TypeReflector(Stringable::class)->isStringable())->toBeTrue()
        ->and(new TypeReflector(stdClass::class)->isStringable())->toBeFalse()
        ->and(new TypeReflector(stdClass::class)->isClass())->toBeTrue()->and(new TypeReflector(Countable::class)->isInterface())->toBeTrue()
        ->and(new TypeReflector(RaxosTests\Reflection\UnitEnumValue::class)->isUnitEnum())->toBeTrue()
        ->and(new TypeReflector(RaxosTests\Reflection\BackedEnumValue::class)->isBackedEnum())->toBeTrue()
        ->and(new TypeReflector(RaxosTests\Reflection\BackedEnumValue::class)->isEnum())->toBeTrue()
        ->and(new TypeReflector(stdClass::class)->isEnum())->toBeFalse();
});

it('does not interpret null embedded in a class name as nullability', function (): void {
    $type = new TypeReflector(RaxosTests\Reflection\nullableNamedClass::class);
    expect($type->isNullable())->toBeFalse()->and($type->accepts(null))->toBeFalse();
});

it('compares complete definitions and reflects named classes', function (): void {
    $type = new TypeReflector('?string');
    expect($type->equals('?string'))->toBeTrue()->and($type->equals(new TypeReflector('?string')))->toBeTrue()
        ->and($type->equals('string'))->toBeFalse()->and($type->isNullable())->toBeTrue()
        ->and(new TypeReflector(stdClass::class)->class()->getName())->toBe(stdClass::class)
        ->and(new TypeReflector(RaxosTests\Reflection\UnitChild::class)->getShortName())->toBe('UnitChild')
        ->and(new TypeReflector('MissingType')->accepts(1))->toBeFalse();
});
