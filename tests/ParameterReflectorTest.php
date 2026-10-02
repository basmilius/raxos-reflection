<?php
declare(strict_types=1);

use Raxos\Reflection\{FunctionReflector, MethodReflector};
use RaxosTests\Reflection\{UnitChild, UnitMarker};

covers(Raxos\Reflection\ParameterReflector::class);

it('exposes class, function, attributes, required status and default values', function (): void {
    $method = new MethodReflector(new ReflectionMethod(UnitChild::class, 'method'));
    $required = $method->getParameter('required');
    $nullable = $method->getParameter('nullable');
    $variadic = $method->getParameter('values');
    expect($required->getClass()->getName())->toBe(UnitChild::class)->and($required->getFunction())->toBeInstanceOf(MethodReflector::class)
        ->and($required->isRequired())->toBeTrue()->and($required->isOptional())->toBeFalse()->and($required->isNullable())->toBeFalse()
        ->and($required->hasDefaultValue())->toBeFalse()->and($required->hasType())->toBeTrue()
        ->and($required->getAttribute(UnitMarker::class)->value)->toBe('parameter')
        ->and($nullable->isNullable())->toBeTrue()->and($nullable->isRequired())->toBeFalse()
        ->and($nullable->hasDefaultValue())->toBeTrue()->and($nullable->getDefaultValue())->toBeNull()
        ->and($variadic->isVariadic())->toBeTrue()->and($variadic->isOptional())->toBeTrue();
});

it('reflects iterable closure parameters without a declaring class', function (): void {
    $parameter = new FunctionReflector(new ReflectionFunction('array_values'))->getParameter(0);
    expect($parameter->getClass())->toBeNull()->and($parameter->getFunction())->toBeInstanceOf(FunctionReflector::class)
        ->and($parameter->isIterable())->toBeTrue();
});
