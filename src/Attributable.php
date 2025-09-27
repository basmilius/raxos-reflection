<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;
use ReflectionProperty;
use function array_map;

/**
 * Trait Attributable
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Reflection
 * @since 2.0.0
 */
trait Attributable
{

    public protected(set) readonly ReflectionClass|ReflectionFunction|ReflectionMethod|ReflectionParameter|ReflectionProperty $reflection;

    /**
     * Returns an attribute instance.
     *
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $name
     * @param bool $recursive
     *
     * @return TAttribute|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getAttribute(string $name, bool $recursive = false): ?object
    {
        $attribute = $this->getRawAttribute($name);
        $instance = $attribute?->newInstance();

        if ($instance || !$recursive) {
            return $instance;
        }

        if ($this instanceof ClassReflector) {
            foreach ($this->getInterfaces() as $interface) {
                $instance = $interface->class()->getAttribute($name);

                if ($instance !== null) {
                    break;
                }
            }

            if ($instance === null && ($parent = $this->getParent())) {
                $instance = $parent->getAttribute($name, true);
            }
        }

        return $instance;
    }

    /**
     * Returns attribute instances.
     *
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $name
     *
     * @return TAttribute[]
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getAttributes(string $name): array
    {
        return array_map(
            static fn(ReflectionAttribute $attribute) => $attribute->newInstance(),
            $this->reflection->getAttributes($name, ReflectionAttribute::IS_INSTANCEOF)
        );
    }

    /**
     * Returns a raw attribute.
     *
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $name
     *
     * @return ReflectionAttribute<TAttribute>|null
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getRawAttribute(string $name): ?ReflectionAttribute
    {
        return $this->reflection->getAttributes($name, ReflectionAttribute::IS_INSTANCEOF)[0] ?? null;
    }

    /**
     * Returns raw attributes.
     *
     * @template TAttribute of object
     *
     * @param class-string<TAttribute> $name
     *
     * @return ReflectionAttribute<TAttribute>[]
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getRawAttributes(string $name): array
    {
        return $this->reflection->getAttributes($name, ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * Returns TRUE if the given attribute is present.
     *
     * @param class-string $name
     * @param bool $instanceOf
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function hasAttribute(string $name, bool $instanceOf = false): bool
    {
        return !empty($this->reflection->getAttributes($name, $instanceOf ? ReflectionAttribute::IS_INSTANCEOF : 0));
    }

}
