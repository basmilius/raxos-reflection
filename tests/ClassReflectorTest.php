<?php
declare(strict_types=1);

use Raxos\Reflection\ClassReflector;
use RaxosTests\Reflection\{UnitChild, UnitParent};

covers(ClassReflector::class);

it('accepts names, instances and existing native or wrapped reflectors', function (): void {
    $native = new ReflectionClass(UnitChild::class);
    $wrapped = new ClassReflector($native);
    foreach ([UnitChild::class, new UnitChild(), $native, $wrapped] as $input) {
        $class = new ClassReflector($input);
        expect($class->getName())->toBe(UnitChild::class)->and($class->getShortName())->toBe('UnitChild')
            ->and($class->getParent()->getName())->toBe(UnitParent::class)->and($class->getFileName())->toEndWith('Units.php')
            ->and($class->getType()->getName())->toBe(UnitChild::class)->and($class->isInstantiable())->toBeTrue();
    }
    expect(new ClassReflector(stdClass::class)->getParent())->toBeNull()
        ->and(new ClassReflector(Countable::class)->isInstantiable())->toBeFalse();
});

it('enumerates members with visibility filters and locates optional members', function (): void {
    $class = new ClassReflector(UnitChild::class);
    $names = static fn(iterable $members): array => array_map(static fn($member): string => $member->getName(), iterator_to_array($members));
    expect($names($class->getPublicProperties()))->not->toContain('private', 'protected')
        ->and($names($class->getProperties()))->toContain('private', 'protected', 'promoted')
        ->and($names($class->getPublicMethods()))->toContain('count', 'method', 'staticValue')
        ->and($names($class->getMethods()))->toContain('__construct')
        ->and($class->getConstructor()->getName())->toBe('__construct')
        ->and(new ClassReflector(stdClass::class)->getConstructor())->toBeNull()
        ->and($class->hasProperty('missing'))->toBeFalse()->and($class->getProperty('missing'))->toBeNull()
        ->and($class->getMethod('missing'))->toBeNull()
        ->and($names($class->getInterfaces()))->toBe([Countable::class]);
});

it('instantiates classes, skips constructors on request and invokes static methods', function (): void {
    $class = new ClassReflector(UnitChild::class);
    expect($class->newInstanceArgs([9])->promoted)->toBe(9)
        ->and($class->getProperty('promoted')->isInitialized($class->newInstanceWithoutConstructor()))->toBeFalse()
        ->and($class->callStatic('staticValue', 21))->toBe(42)
        ->and($class->implements(Countable::class))->toBeTrue()->and($class->is(UnitParent::class))->toBeTrue()
        ->and($class->is(stdClass::class))->toBeFalse();
});
