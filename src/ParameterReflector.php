<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use Raxos\Contract\Reflection\ReflectorInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionMethod;
use ReflectionParameter;

/**
 * Class ParameterReflector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Reflection
 * @since 2.0.0
 */
final readonly class ParameterReflector implements ReflectorInterface
{

    use Attributable;

    /**
     * ParameterReflector constructor.
     *
     * @param ReflectionParameter $parameter
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(ReflectionParameter $parameter)
    {
        $this->reflection = $parameter;
    }

    /**
     * Returns the declaring class reflector.
     *
     * @return ClassReflector|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getClass(): ?ClassReflector
    {
        $class = $this->reflection->getDeclaringClass();

        if ($class !== null) {
            return new ClassReflector($class);
        }

        return null;
    }

    /**
     * Returns the declaring function reflector.
     *
     * @return FunctionReflector|MethodReflector
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getFunction(): FunctionReflector|MethodReflector
    {
        $function = $this->reflection->getDeclaringFunction();

        return match (true) {
            $function instanceof ReflectionMethod => new MethodReflector($function),
            $function instanceof ReflectionFunction => new FunctionReflector($function),
            default => throw new ReflectionException('Unknown function type.'),
        };
    }

    /**
     * Returns the default value of the parameter.
     *
     * @return mixed
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getDefaultValue(): mixed
    {
        return $this->reflection->getDefaultValue();
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getName(): string
    {
        return $this->reflection->getName();
    }

    /**
     * Returns the position of the parameter.
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getPosition(): int
    {
        return $this->reflection->getPosition();
    }

    /**
     * Returns a reflector for the parameter type.
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
     * Returns TRUE if the parameter has a default value.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function hasDefaultValue(): bool
    {
        return $this->reflection->isDefaultValueAvailable();
    }

    /**
     * Returns TRUE if the parameter has a type.
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
     * Returns TRUE if the parameter type is iterable.
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
     * Returns TRUE if the parameter is nullable.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public function isNullable(): bool
    {
        return $this->getType()->isNullable();
    }

    /**
     * Returns TRUE if the parameter is optional.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isOptional(): bool
    {
        return $this->reflection->isOptional();
    }

    /**
     * Returns TRUE if the parameter is required.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public function isRequired(): bool
    {
        return !$this->isNullable() && !$this->reflection->isOptional();
    }

    /**
     * Returns TRUE if the parameter is variadic.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isVariadic(): bool
    {
        return $this->reflection->isVariadic();
    }

}
