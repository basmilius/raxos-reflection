<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use Closure;
use Generator;
use Raxos\Contract\Reflection\ReflectorInterface;
use ReflectionException;
use ReflectionFunction;
use ReflectionParameter;

/**
 * Class FunctionReflector
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Reflection
 * @since 2.0.0
 */
final readonly class FunctionReflector implements ReflectorInterface
{

    use Attributable;

    /**
     * FunctionReflector constructor.
     *
     * @param ReflectionFunction|Closure $function
     *
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(ReflectionFunction|Closure $function)
    {
        if ($function instanceof Closure) {
            $this->reflection = new ReflectionFunction($function);
        } else {
            $this->reflection = $function;
        }
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
     * Returns the short name of the function.
     *
     * @return string
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getShortName(): string
    {
        return $this->reflection->getShortName();
    }

    /**
     * Returns the filename where the function is defined.
     *
     * @return string|false|false|false
     * @author Bas Milius <bas@mili.us>
     * @since 3.2.0
     */
    public function getFileName(): string|false
    {
        return $this->reflection->getFileName();
    }

    /**
     * Returns the ending line of the function declaration.
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getEndLine(): int
    {
        return (int)$this->reflection->getEndLine();
    }

    /**
     * Returns the start line of the function declaration.
     *
     * @return int
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getStartLine(): int
    {
        return (int)$this->reflection->getStartLine();
    }

    /**
     * Invokes the function with the given args.
     *
     * @param array $args
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function invokeArgs(array $args = []): mixed
    {
        return $this->reflection->invokeArgs($args);
    }

    /**
     * Returns the parameters of the function.
     *
     * @return Generator
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
     * Gets a single parameter of the function.
     *
     * @param int|string $key
     *
     * @return ParameterReflector|null
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getParameter(int|string $key): ?ParameterReflector
    {
        $parameter = array_find(
            $this->reflection->getParameters(),
            static fn(ReflectionParameter $parameter) => $parameter->getName() === $key || $parameter->getPosition() === $key,
        );

        if ($parameter === null) {
            return null;
        }

        return new ParameterReflector($parameter);
    }

}
