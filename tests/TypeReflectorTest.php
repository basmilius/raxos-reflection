<?php
declare(strict_types=1);

use Raxos\Reflection\TypeReflector;

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
