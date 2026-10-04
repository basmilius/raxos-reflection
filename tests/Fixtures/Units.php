<?php
declare(strict_types=1);

namespace RaxosTests\Reflection;

use Attribute;
use Countable;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_FUNCTION | Attribute::TARGET_PARAMETER | Attribute::TARGET_PROPERTY | Attribute::IS_REPEATABLE)]
class UnitMarker
{

    public function __construct(public string $value) {}

}

#[Attribute(Attribute::TARGET_CLASS)]
final class DerivedMarker extends UnitMarker {}

#[UnitMarker('interface')]
interface MarkedContract {}

#[UnitMarker('parent')]
class UnitParent
{

    public static function staticValue(int $value): int
    {
        return $value * 2;
    }

}

final class UnitChild extends UnitParent implements Countable
{

    #[UnitMarker('property')]
    public ?string $nullable = null;
    public int $uninitialized;
    protected string $protected = 'protected';
    private string $private = 'private';
    public readonly string $readonly;
    public mixed $untyped;

    public function __construct(public int $promoted = 4) {}

    public function count(): int
    {
        return 3;
    }

    public function method(#[UnitMarker('parameter')] int $required, ?string $nullable = null, string ...$values): array
    {
        return [$required, $nullable, $values];
    }

    public function noReturnType() {}

}

final class MarkedImplementation implements MarkedContract {}

#[UnitMarker('one')]
#[UnitMarker('two')]
#[DerivedMarker('derived')]
final class RepeatedMarkers {}

final class nullableNamedClass {}

enum UnitEnumValue
{

    case UNIT;

}

enum BackedEnumValue: string
{

    case UNIT = 'unit';

}
