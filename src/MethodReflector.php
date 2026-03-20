<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use Generator;
use Raxos\Contract\Reflection\ReflectorInterface;
use Raxos\Contract\SerializableInterface;
use ReflectionException;
use ReflectionMethod;
use function implode;

/**
 * Class MethodReflector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Reflection
 * @since 2.0.0
 */
final readonly class MethodReflector implements ReflectorInterface, SerializableInterface
{

    use Attributable;

    /**
     * MethodReflector constructor.
     *
     * @param ReflectionMethod $method
     *
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(ReflectionMethod $method)
    {
        $this->reflection = $method;
    }

    /**
     * Returns the parameters of the method.
     *
     * @return Generator<ParameterReflector>
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getParameters(): Generator
    {
        foreach ($this->reflection->getParameters() as $parameter) {
            yield new ParameterReflector($parameter);
        }
    }

    /**
     * Returns a parameter of the method.
     *
     * @param string $name
     *
     * @return ParameterReflector|null
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getParameter(string $name): ?ParameterReflector
    {
        foreach ($this->getParameters() as $parameter) {
            if ($parameter->getName() === $name) {
                return $parameter;
            }
        }

        return null;
    }

    /**
     * Returns the reflector for the declaring class.
     *
     * @return ClassReflector
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getClass(): ClassReflector
    {
        return reflect($this->reflection->getDeclaringClass());
    }

    /**
     * Returns the type reflector for the return type of the method.
     *
     * @return TypeReflector|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getReturnType(): ?TypeReflector
    {
        $type = $this->reflection->getReturnType();

        if ($type === null) {
            return null;
        }

        return new TypeReflector($type);
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
     * Returns the short name of the method.
     *
     * @return string
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getShortName(): string
    {
        $parameters = [];
        $str = $this->getClass()->getShortName() . '::' . $this->getName() . '(';

        foreach ($this->getParameters() as $parameter) {
            $parameters[] = $parameter->getType()->getShortName() . ' $' . $parameter->getName();
        }

        $str .= implode(', ', $parameters);

        return $str . ')';
    }

    /**
     * Invoke the method with the specific instance with the given args.
     *
     * @param object|null $instance
     * @param array $args
     *
     * @return mixed
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function invokeArgs(?object $instance, array $args = []): mixed
    {
        return $this->reflection->invokeArgs($instance, $args);
    }

    /**
     * {@inheritdoc}
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __serialize(): array
    {
        return [
            $this->reflection->getDeclaringClass()->getName(),
            $this->reflection->getName()
        ];
    }

    /**
     * {@inheritdoc}
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __unserialize(array $data): void
    {
        $this->reflection = new ReflectionMethod($data[0], $data[1]);
    }

}
