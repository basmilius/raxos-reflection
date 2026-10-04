<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use Error;
use Raxos\Contract\Reflection\ReflectorInterface;
use ReflectionException;
use ReflectionProperty;
use function ltrim;
use function preg_match;

/**
 * Class PropertyReflector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Reflection
 * @since 2.0.0
 */
final readonly class PropertyReflector implements ReflectorInterface
{

    use Attributable;

    /**
     * PropertyReflector constructor.
     *
     * @param ReflectionProperty $property
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(ReflectionProperty $property)
    {
        $this->reflection = $property;
    }

    /**
     * Returns TRUE if the property accepts the input.
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
        return $this->getType()->accepts($input);
    }

    /**
     * Returns the value of the property, or a default value.
     *
     * @param object $instance
     * @param mixed|null $default
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getValue(
        object $instance,
        mixed $default = null
    ): mixed
    {
        try {
            return $this->reflection->getValue($instance) ?? $default;
        } catch (Error $err) {
            return $default ?? throw $err;
        }
    }

    /**
     * Sets the value of the property.
     *
     * @param object $instance
     * @param mixed $value
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function setValue(
        object $instance,
        mixed $value
    ): void
    {
        $this->reflection->setValue($instance, $value);
    }

    /**
     * Unsets the property.
     *
     * @param object $instance
     *
     * @return void
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function unsetValue(object $instance): void
    {
        unset($instance->{$this->getName()});
    }

    /**
     * Returns TRUE if the property is initialized.
     *
     * @param object $instance
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isInitialized(object $instance): bool
    {
        return $this->reflection->isInitialized($instance);
    }

    /**
     * Returns the declaring class reflector.
     *
     * @return ClassReflector
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getClass(): ClassReflector
    {
        return new ClassReflector($this->reflection->getDeclaringClass());
    }

    /**
     * Returns property type reflector.
     *
     * @return TypeReflector
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getType(): TypeReflector
    {
        return new TypeReflector($this->reflection);
    }

    /**
     * {@inheritdoc}
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getName(): string
    {
        return $this->reflection->getName();
    }

    /**
     * Returns the default value of the property.
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getDefaultValue(): mixed
    {
        if ($this->isPromoted()) {
            $parameter = $this->getClass()->getConstructor()?->getParameter($this->getName());

            if ($parameter?->hasDefaultValue()) {
                return $parameter->getDefaultValue();
            }
        }

        return $this->reflection->getDefaultValue();
    }

    /**
     * Returns TRUE if the property has a default value.
     *
     * @return bool
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function hasDefaultValue(): bool
    {
        if ($this->reflection->hasDefaultValue()) {
            return true;
        }

        if (!$this->isPromoted()) {
            return false;
        }

        $classRef = $this->getClass();
        $constructorRef = $classRef->getConstructor();

        if ($constructorRef === null) {
            return false;
        }

        foreach ($constructorRef->getParameters() as $parameter) {
            if ($parameter->getName() === $this->getName()) {
                return $parameter->hasDefaultValue();
            }
        }

        return false;
    }

    /**
     * Returns TRUE if the property has a type.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function hasType(): bool
    {
        return $this->reflection->hasType();
    }

    /**
     * Returns TRUE if the property is iterable.
     *
     * @return bool
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isIterable(): bool
    {
        return $this->getType()->isIterable();
    }

    /**
     * Returns TRUE if the property is promoted.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isPromoted(): bool
    {
        return $this->reflection->isPromoted();
    }

    /**
     * Returns TRUE if the property is nullable.
     *
     * @return bool
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isNullable(): bool
    {
        return $this->getType()->isNullable();
    }

    /**
     * Returns TRUE if the property is public.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isPublic(): bool
    {
        return $this->reflection->isPublic();
    }

    /**
     * Returns TRUE if the property is protected.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isProtected(): bool
    {
        return $this->reflection->isProtected();
    }

    /**
     * Returns TRUE if the property is private.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isPrivate(): bool
    {
        return $this->reflection->isPrivate();
    }

    /**
     * Returns TRUE if the property is readonly.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isReadonly(): bool
    {
        return $this->reflection->isReadOnly();
    }

    /**
     * Returns TRUE if the property is virtual.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isVirtual(): bool
    {
        return $this->reflection->isVirtual();
    }

    /**
     * Returns iterated type.
     *
     * @return TypeReflector|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getIterableType(): ?TypeReflector
    {
        $doc = $this->reflection->getDocComment();

        if (!$doc) {
            return null;
        }

        preg_match('/@var ([\\\\\w]+)\[]/', $doc, $match);

        if (!isset($match[1])) {
            return null;
        }

        return new TypeReflector(ltrim($match[1], '\\'));
    }

}
