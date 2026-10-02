<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use BackedEnum;
use Generator;
use Iterator;
use Raxos\Contract\Reflection\ReflectorInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionIntersectionType;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionType;
use ReflectionUnionType;
use Reflector;
use Stringable;
use UnitEnum;
use function array_all;
use function array_any;
use function array_key_exists;
use function array_key_last;
use function array_map;
use function call_user_func;
use function class_exists;
use function explode;
use function implode;
use function in_array;
use function interface_exists;
use function is_a;
use function is_iterable;
use function is_string;
use function preg_replace;
use function str_contains;
use function str_replace;

/**
 * Class TypeReflector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Reflection
 * @since 2.0.0
 */
final readonly class TypeReflector implements ReflectorInterface
{

    private const array BUILTINS = [
        'array' => 'is_array',
        'bool' => 'is_bool',
        'callable' => 'is_callable',
        'float' => 'is_float',
        'int' => 'is_int',
        'null' => 'is_null',
        'object' => 'is_object',
        'resource' => 'is_resource',
        'string' => 'is_string',
        'false' => null,
        'mixed' => null,
        'never' => null,
        'true' => null,
        'void' => null
    ];

    private const array SCALARS = [
        'bool',
        'float',
        'int',
        'string'
    ];

    private string $definition;
    private string $definitionNormalized;
    public private(set) bool $isNullable;

    /**
     * TypeReflector constructor.
     *
     * @param Reflector|ReflectionType|string $type
     *
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(Reflector|ReflectionType|string $type)
    {
        $this->definition = preg_replace('/^\(([^()]*)\)$/', '$1', $this->resolveDefinition($type));
        $this->definitionNormalized = str_replace('?', '', $this->definition);
        $this->isNullable = $this->resolveIsNullable($type);
    }

    /**
     * Returns TRUE if the type accepts the input.
     *
     * @param mixed $input
     *
     * @return bool
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function accepts(mixed $input): bool
    {
        if ($this->isNullable && $input === null) {
            return true;
        }

        if ($this->isBuiltIn()) {
            return match ($this->definitionNormalized) {
                'false' => $input === false,
                'mixed' => true,
                'never', 'void' => false,
                'true' => $input === true,
                default => call_user_func(self::BUILTINS[$this->definitionNormalized], $input)
            };
        }

        if ($this->isClass() || $this->isInterface()) {
            if (is_string($input)) {
                return $this->matches($input);
            }

            return $input instanceof $this->definitionNormalized;
        }

        if ($this->isIterable()) {
            return is_iterable($input);
        }

        if (str_contains($this->definition, '|')) {
            return array_any($this->split(), static fn(self $type) => $type->accepts($input));
        }

        if (str_contains($this->definition, '&')) {
            return array_all($this->split(), static fn(self $type) => $type->accepts($input));
        }

        return false;
    }

    /**
     * Returns TRUE if the type matches the given type.
     *
     * @param string $type
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function matches(string $type): bool
    {
        return is_a($this->definitionNormalized, $type, true);
    }

    /**
     * Returns a class reflector for the type.
     *
     * @return ClassReflector
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function class(): ClassReflector
    {
        return new ClassReflector($this->definitionNormalized);
    }

    /**
     * Returns TRUE if the types are the same.
     *
     * @param string|TypeReflector $type
     *
     * @return bool
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function equals(string|self $type): bool
    {
        if (is_string($type)) {
            $type = new self($type);
        }

        return $this->definition === $type->definition;
    }

    /**
     * Returns TRUE if the type is built-in.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isBuiltIn(): bool
    {
        return array_key_exists($this->definitionNormalized, self::BUILTINS);
    }

    /**
     * Returns TRUE if the type is nullable.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isNullable(): bool
    {
        return $this->isNullable;
    }

    /**
     * Returns TRUE if the type is scalar.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isScalar(): bool
    {
        return in_array($this->definitionNormalized, self::SCALARS, true);
    }

    /**
     * Returns TRUE if the type is a class.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isClass(): bool
    {
        return class_exists($this->definitionNormalized) && !interface_exists($this->definitionNormalized);
    }

    /**
     * Returns TRUE if the type is an interface.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isInterface(): bool
    {
        return interface_exists($this->definitionNormalized);
    }

    /**
     * Returns TRUE if the type is an enum.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isEnum(): bool
    {
        return $this->isUnitEnum() || $this->isBackedEnum();
    }

    /**
     * Returns TRUE if the type is a backed enum.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isBackedEnum(): bool
    {
        return $this->matches(BackedEnum::class);
    }

    /**
     * Returns TRUE if the type is a unit enum.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isUnitEnum(): bool
    {
        return $this->matches(UnitEnum::class);
    }

    /**
     * Returns TRUE if the type is iterable.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isIterable(): bool
    {
        if ($this->matches(Iterator::class)) {
            return true;
        }

        return in_array($this->definitionNormalized, [
            'array',
            'iterable',
            Generator::class
        ], true);
    }

    /**
     * Returns TRUE if the type is stringable.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isStringable(): bool
    {
        if ($this->matches(Stringable::class)) {
            return true;
        }

        return $this->definitionNormalized === 'string';
    }

    /**
     * Splits the type into multiple types.
     *
     * @return self[]
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function split(): array
    {
        return array_map(
            static fn(string $type) => new self($type),
            explode(str_contains($this->definition, '|') ? '|' : '&', $this->definition)
        );
    }

    /**
     * Returns the name of the type.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getName(): string
    {
        return $this->definition;
    }

    /**
     * Returns the short name of the type.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getShortName(): string
    {
        $parts = explode('\\', $this->definition);

        return $parts[array_key_last($parts)];
    }

    /**
     * Resolves the type definition.
     *
     * @param Reflector|ReflectionType|string $type
     *
     * @return string
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    private function resolveDefinition(Reflector|ReflectionType|string $type): string
    {
        if (is_string($type)) {
            return $type;
        }

        if ($type instanceof ReflectionParameter || $type instanceof ReflectionProperty) {
            return $type->getType() === null ? 'mixed' : $this->resolveDefinition($type->getType());
        }

        if ($type instanceof ReflectionClass) {
            return $type->getName();
        }

        if ($type instanceof ReflectionNamedType) {
            return $type->getName();
        }

        if ($type instanceof ReflectionUnionType) {
            return implode('|', array_map(
                fn(ReflectionType $part): string => $part instanceof ReflectionIntersectionType ? '(' . $this->resolveDefinition($part) . ')' : $this->resolveDefinition($part),
                $type->getTypes()
            ));
        }

        if ($type instanceof ReflectionIntersectionType) {
            return implode('&', array_map(
                $this->resolveDefinition(...),
                $type->getTypes()
            ));
        }

        throw new ReflectionException('Could not resolve type definition.');
    }

    /**
     * Resolves if the type is nullable.
     *
     * @param Reflector|ReflectionType|string $type
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    private function resolveIsNullable(Reflector|ReflectionType|string $type): bool
    {
        if (is_string($type)) {
            return $type === 'mixed' || str_starts_with($type, '?') || preg_match('/(?:^|[|(&])null(?:$|[|)&])/', $type) === 1;
        }

        if ($type instanceof ReflectionParameter || $type instanceof ReflectionProperty) {
            return $type->getType()?->allowsNull() ?? true;
        }

        if ($type instanceof ReflectionType) {
            return $type->allowsNull();
        }

        return false;
    }

}
