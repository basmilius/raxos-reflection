<?php
declare(strict_types=1);

namespace RaxosTests\Reflection;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final readonly class Marker
{
    public function __construct(public string $value) {}
}

#[Marker('sample')]
final class Sample
{
    public function __construct(public int $value = 2) {}

    #[Marker('method')]
    public function multiply(int $factor = 3): int
    {
        return $this->value * $factor;
    }
}
