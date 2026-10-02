<?php
declare(strict_types=1);

use Raxos\Reflection\FunctionReflector;

covers(FunctionReflector::class);

it('wraps closures and native reflections and locates parameters by index or name', function (): void {
    $closure = static fn (int $count = 3, ?string $label = null): string => ($label ?? 'unit') . ':' . $count;
    foreach ([$closure, new ReflectionFunction($closure)] as $input) {
        $function = new FunctionReflector($input);
        expect($function->invokeArgs())->toBe('unit:3')->and($function->invokeArgs(['count' => 4, 'label' => 'custom']))->toBe('custom:4')
            ->and($function->getParameter(0)->getName())->toBe('count')
            ->and($function->getParameter('label')->getPosition())->toBe(1)
            ->and($function->getParameter('missing'))->toBeNull()->and($function->getParameter(9))->toBeNull()
            ->and(iterator_to_array($function->getParameters()))->toHaveCount(2)
            ->and($function->getName())->toBe($function->getShortName())
            ->and($function->getFileName())->toBe(__FILE__)
            ->and($function->getStartLine())->toBeGreaterThan(0)->and($function->getEndLine())->toBeGreaterThanOrEqual($function->getStartLine());
    }
});

it('supports builtin functions that have no source file', function (): void {
    $function = new FunctionReflector(new ReflectionFunction('strlen'));
    expect($function->invokeArgs(['unit']))->toBe(4)->and($function->getFileName())->toBeFalse()
        ->and($function->getStartLine())->toBe(0)->and($function->getEndLine())->toBe(0);
});
