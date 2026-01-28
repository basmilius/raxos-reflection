<?php
declare(strict_types=1);

namespace Raxos\Reflection;

use ReflectionClass;
use ReflectionException;

/**
 * Returns a new class reflector for the given class.
 *
 * @template TClass of object
 *
 * @param class-string<TClass>|ReflectionClass<TClass> $class
 *
 * @return ClassReflector<TClass>
 * @throws ReflectionException
 * @author Bas Milius <bas@mili.us>
 * @since 2.0.0
 */
function reflect(string|ReflectionClass $class): ClassReflector
{
    return new ClassReflector($class);
}
