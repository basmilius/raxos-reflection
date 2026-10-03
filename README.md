<a href="https://bas.dev">
    <img src="https://bmcdn.nl/assets/branding/logo.svg" alt="Bas Milius" height="48" />
</a>

---

# Raxos Reflection

Typed wrappers around PHP reflection for classes, members, parameters, functions and types.

[Documentation](https://raxos.dev/reflection/) | [Packagist](https://packagist.org/packages/raxos/reflection) | [Raxos](https://github.com/basmilius/raxos)

- Class hierarchy, attribute and member inspection.
- Nullable, literal, union and intersection type handling, including DNF types.
- Generators of reflector objects and callable invocation helpers.

## Installation

Requires PHP 8.5 or later. Composer checks the remaining package and extension dependencies declared in [composer.json](composer.json).

```sh
composer require "raxos/reflection:^3.2"
```

## Usage

```php
<?php
declare(strict_types=1);

use function Raxos\Reflection\reflect;

require __DIR__ . '/vendor/autoload.php';

final readonly class Product
{
    public function __construct(public int $id, public string $name) {}
}

$class = reflect(Product::class);

foreach ($class->getPublicProperties() as $property) {
    echo $property->getName() . ': ' . $property->getType()->getName() . PHP_EOL;
}
```

`getMethod()` and `getProperty()` return `null` for missing members. Untyped parameters resolve to `mixed`; internal functions may return `false` for their source filename.

## Documentation

- [Reflectors](https://raxos.dev/reflection/reflectors)
- [Working with types](https://raxos.dev/reflection/types)
- [Reading attributes](https://raxos.dev/reflection/attributes)

## Testing

Run this library's Pest suite from the Raxos workspace:

```sh
git clone --recurse-submodules https://github.com/basmilius/raxos.git
cd raxos
composer install
vendor/bin/pest --testsuite=reflection
```

See [Testing Raxos](https://github.com/basmilius/raxos/blob/main/TESTING.md) for PHP extensions, integration services and coverage commands. The library's [Tests workflow](.github/workflows/tests.yml) also runs in GitHub Actions.

## License

[MIT](LICENSE). Copyright (c) 2017 - present Bas Milius.
