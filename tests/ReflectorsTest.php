<?php
declare(strict_types=1);

use Raxos\Reflection\{ClassReflector, FunctionReflector, TypeReflector};
use RaxosTests\Reflection\{Marker, Sample};

it('reflects classes, attributes, properties and invokable methods', function (): void {
    $class = new ClassReflector(Sample::class);
    $sample = $class->newInstanceArgs([4]);
    expect($class->getName())->toBe(Sample::class)->and($class->getShortName())->toBe('Sample')
        ->and($class->getAttribute(Marker::class)->value)->toBe('sample')
        ->and($class->hasProperty('value'))->toBeTrue()->and($class->getProperty('missing'))->toBeNull()
        ->and($class->getMethod('missing'))->toBeNull()
        ->and($class->getMethod('multiply')->invokeArgs($sample, [5]))->toBe(20)
        ->and($class->getMethod('multiply')->getAttribute(Marker::class)->value)->toBe('method');
    $property = $class->getProperty('value');
    expect($property->getValue($sample))->toBe(4)->and($property->accepts(5))->toBeTrue()->and($property->accepts('5'))->toBeFalse();
    $property->setValue($sample, 7);
    expect($sample->value)->toBe(7);
});

it('reflects callable parameters by name and position, including defaults and nullability', function (): void {
    $reflector = new FunctionReflector(static fn(int $count = 3, ?string $name = null): string => ($name ?? 'default') . ':' . $count);
    expect($reflector->invokeArgs())->toBe('default:3')
        ->and($reflector->getParameter(0)->getName())->toBe('count')
        ->and($reflector->getParameter('count')->getDefaultValue())->toBe(3)
        ->and($reflector->getParameter('name')->getType()->accepts(null))->toBeTrue()
        ->and($reflector->getParameter('missing'))->toBeNull();
});

it('preserves intersection groups in DNF union types', function (): void {
    $function = new ReflectionFunction(static fn((Countable&Iterator)|Stringable $value): mixed => $value);
    $type = new TypeReflector($function->getParameters()[0]->getType());
    expect($type->accepts(new ArrayIterator([1])))->toBeTrue()
        ->and($type->accepts(new ArrayObject([1])))->toBeFalse()
        ->and($type->accepts(new class implements Stringable {

            public function __toString(): string
            {
                return 'value';
            }

        }))->toBeTrue();
});
