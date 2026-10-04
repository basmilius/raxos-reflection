<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use Generator;
use Raxos\Contract\Reflection\ReflectorInterface;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionProperty;
use function is_string;

/**
 * Class ClassReflector
 *
 * @template TClass of object
 *
 * @author Bas Milius <bas@mili.us>
 * @package Raxos\Reflection
 * @since 2.0.0
 */
final readonly class ClassReflector implements ReflectorInterface
{

    use Attributable;

    /**
     * ClassReflector constructor.
     *
     * @param class-string<TClass>|TClass $class
     *
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function __construct(string|object $class)
    {
        if (is_string($class)) {
            $this->reflection = new ReflectionClass($class);
        } elseif ($class instanceof self) {
            $this->reflection = $class->reflection;
        } elseif (!($class instanceof ReflectionClass)) {
            $this->reflection = new ReflectionClass($class);
        } else {
            $this->reflection = $class;
        }
    }

    /**
     * {@inheritdoc}
     * @return class-string<TClass>
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getName(): string
    {
        return $this->reflection->getName();
    }

    /**
     * Returns the short name of the class.
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
     * Returns the file name where the class is defined.
     *
     * @return string|false
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getFileName(): string|false
    {
        return $this->reflection->getFileName();
    }

    /**
     * Returns the interfaces the class implements.
     *
     * @return Generator<TypeReflector>
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getInterfaces(): Generator
    {
        foreach ($this->reflection->getInterfaces() as $interface) {
            yield new TypeReflector($interface);
        }
    }

    /**
     * Returns the reflector for the parent class.
     *
     * @return self|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getParent(): ?self
    {
        if ($parent = $this->reflection->getParentClass()) {
            return new self($parent);
        }

        return null;
    }

    /**
     * Returns a type reflector for the class.
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
     * Returns the public properties of the class.
     *
     * @return Generator<PropertyReflector>
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getPublicProperties(): Generator
    {
        foreach ($this->reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $property) {
            yield new PropertyReflector($property);
        }
    }

    /**
     * Returns the properties of the class.
     *
     * @return Generator<PropertyReflector>
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getProperties(): Generator
    {
        foreach ($this->reflection->getProperties() as $property) {
            yield new PropertyReflector($property);
        }
    }

    /**
     * Returns a reflector for a property of the class.
     *
     * @param string $name
     *
     * @return PropertyReflector|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getProperty(string $name): ?PropertyReflector
    {
        return $this->reflection->hasProperty($name) ? new PropertyReflector($this->reflection->getProperty($name)) : null;
    }

    /**
     * Returns TRUE if the class has the given property.
     *
     * @param string $name
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function hasProperty(string $name): bool
    {
        return $this->reflection->hasProperty($name);
    }

    /**
     * Returns the constructor of the class.
     *
     * @return MethodReflector|null
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getConstructor(): ?MethodReflector
    {
        $constructor = $this->reflection->getConstructor();

        if ($constructor !== null) {
            return new MethodReflector($constructor);
        }

        return null;
    }

    /**
     * Returns the public methods of the class.
     *
     * @return Generator<MethodReflector>
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getPublicMethods(): Generator
    {
        foreach ($this->reflection->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            yield new MethodReflector($method);
        }
    }

    /**
     * Returns the methods of the class.
     *
     * @return Generator
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getMethods(): Generator
    {
        foreach ($this->reflection->getMethods() as $method) {
            yield new MethodReflector($method);
        }
    }

    /**
     * Returns a method reflector for a method of the class.
     *
     * @param string $name
     *
     * @return MethodReflector|null
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function getMethod(string $name): ?MethodReflector
    {
        return $this->reflection->hasMethod($name) ? new MethodReflector($this->reflection->getMethod($name)) : null;
    }

    /**
     * Returns TRUE if the class is instantiable.
     *
     * @return bool
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function isInstantiable(): bool
    {
        return $this->reflection->isInstantiable();
    }

    /**
     * Creates a new instance of the class using the given args.
     *
     * @param array $args
     *
     * @return TClass
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function newInstanceArgs(array $args = []): object
    {
        return $this->reflection->newInstanceArgs($args);
    }

    /**
     * Creates a new instance of the class without calling the constructor.
     *
     * @return TClass
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function newInstanceWithoutConstructor(): object
    {
        return $this->reflection->newInstanceWithoutConstructor();
    }

    /**
     * Calls a static method of the class using the given args.
     *
     * @param string $method
     * @param mixed ...$args
     *
     * @return mixed
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function callStatic(
        string $method,
        mixed ...$args
    ): mixed
    {
        $className = $this->reflection->getName();

        return $className::$method(...$args);
    }

    /**
     * Returns TRUE if the class implements the given interface.
     *
     * @param class-string $interface
     *
     * @return bool
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function implements(string $interface): bool
    {
        return $this->getType()->matches($interface);
    }

    /**
     * Returns TRUE if the class matches the given type.
     *
     * @param class-string|string $type
     *
     * @return bool
     * @throws ReflectionException
     * @author Bas Milius <bas@mili.us>
     * @since 2.0.0
     */
    public function is(string $type): bool
    {
        return $this->getType()->matches($type);
    }
}
