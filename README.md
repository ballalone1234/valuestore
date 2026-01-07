# Easily store some loose values

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/valuestore.svg?style=flat-square)](https://packagist.org/packages/spatie/valuestore)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/spatie/valuestore/master.svg?style=flat-square)](https://travis-ci.org/spatie/valuestore)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/valuestore.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/valuestore)
[![StyleCI](https://styleci.io/repos/53952776/shield?branch=master)](https://styleci.io/repos/53952776)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/valuestore.svg?style=flat-square)](https://packagist.org/packages/spatie/valuestore)

> **✨ Now with full PHP 8.2+ support!** This version includes typed properties, union types, and modern PHP features for better performance and type safety.

This package makes it easy to store and retrieve some loose values. Stored values are saved as a json file.

It can be used like this:

```php
use Spatie\Valuestore\Valuestore;

$valuestore = Valuestore::make($pathToFile);

$valuestore->put('key', 'value');

$valuestore->get('key'); // Returns 'value'

$valuestore->has('key'); // Returns true

// Specify a default value for when the specified key does not exist
$valuestore->get('non existing key', 'default') // Returns 'default'

$valuestore->put('anotherKey', 'anotherValue');

// Put multiple items in one go
$valuestore->put(['ringo' => 'drums', 'paul' => 'bass']);

$valuestore->all(); // Returns an array with all items

$valuestore->forget('key'); // Removes the item

$valuestore->flush(); // Empty the entire valuestore

$valuestore->flushStartingWith('somekey'); // remove all items whose keys start with "somekey"

$valuestore->increment('number'); // $valuestore->get('number') will return 1 
$valuestore->increment('number'); // $valuestore->get('number') will return 2
$valuestore->increment('number', 3); // $valuestore->get('number') will return 5

// Valuestore implements ArrayAccess
$valuestore['key'] = 'value';
$valuestore['key']; // Returns 'value'
isset($valuestore['key']); // Return true
unset($valuestore['key']); // Equivalent to removing the value

// Valuestore implements Countable
count($valuestore); // Returns 0
$valuestore->put('key', 'value');
count($valuestore); // Returns 1
```

Read the [usage](#usage) section of this readme to learn about the other methods.

In [this post on Laravel News](https://laravel-news.com/global-application-settings), [Tim MacDonald](https://twitter.com/timacdonald87) shares how you can use this package to power a `settings` function.

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## Support us

Learn how to create a package like this one, by watching our premium video course:

[![Laravel Package training](https://spatie.be/github/package-training.jpg)](https://laravelpackage.training)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Requirements

- PHP 8.2 or higher
- JSON extension

> **Note**: For PHP 7.2-8.1 support, please use version 1.x of this package.

## Installation

You can install the package via composer:

``` bash
composer require spatie/valuestore
```

### Check System Compatibility

Before installing, you can check if your system meets the requirements:

```bash
php check-php-version.php
```

### Upgrading from 1.x

If you're upgrading from version 1.x, please read the [PHP 8 Upgrade Guide](PHP8_UPGRADE_GUIDE.md) and [Migration Summary](MIGRATION_SUMMARY.md).

## Usage

To create a Valuestore use the `make` method.

```php
$valuestore = Valuestore::make($pathToFile);
```

You can also pass some values as a second argument. These will be added to the valuestore using the `put` method.

```php
$valuestore = Valuestore::make($pathToFile, ['key' => 'value']);
```

All values will be saved as json in the given file.

When there are no values stored, the file will be deleted.

You can call the following methods on the `Valuestore`

### put
```php
/**
 * Put a value in the store.
 */
public function put(string|array $name, mixed $value = null): static
```

### get

```php
/**
 * Get a value from the store.
 */
public function get(string $name, mixed $default = null): mixed
```

### has

```php
/**
 * Determine if the store has a value for the given name.
 */
public function has(string $name): bool
```

### all
```php
/**
 * Get all values from the store.
 */
public function all(): array
```

### allStartingWith
```php
/**
 * Get all values from the store which keys start with the given string.
 */
public function allStartingWith(string $startingWith = ''): array
```

### forget
```php
/**
 * Forget a value from the store.
 */
public function forget(string $key): static
```

### flush
```php
/**
 * Flush all values from the store.
 */
public function flush(): static
```

### flushStartingWith
```php
/**
 * Flush all values from the store which keys start with the specified value.
 */
public function flushStartingWith(string $startingWith = ''): static
```

### pull
```php
/**
 * Get and forget a value from the store.
 */
public function pull(string $name): mixed
```

### increment
```php
/**
 * Increment a value from the store.
 */
public function increment(string $name, int $by = 1): int
```

### decrement
```php
/**
 * Decrement a value from the store.
 */
public function decrement(string $name, int $by = 1): int
```

### push
```php
/**
 * Push a new value into an array.
 */
public function push(string $name, mixed $pushValue): static
```

### prepend
```php
/**
 * Prepend a new value into an array.
 */
public function prepend(string $name, mixed $prependValue): static
```

### count
```php
/**
 * Count elements.
 */
public function count(): int
```

## What's New in 2.0

This version includes major improvements with PHP 8.2+ features:

- ✅ **Typed Properties**: All class properties now have type declarations
- ✅ **Union Types**: Methods like `put()` support `string|array` parameters
- ✅ **Return Types**: All methods have explicit return type declarations
- ✅ **Static Return Type**: Fluent methods return `static` for better IDE support
- ✅ **Mixed Type**: Flexible parameters use the `mixed` type
- ✅ **Modern PHP Functions**: Uses `str_starts_with()` and other PHP 8 functions
- ✅ **PHPUnit 11**: Tests updated to use PHP 8 attributes (`#[Test]`)
- ✅ **Better Performance**: Optimized for PHP 8.2+ JIT compiler

See [CHANGELOG.md](CHANGELOG.md) for a complete list of changes.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information about what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Kruikstraat 22, 2018 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [Jolita Grazyte](https://github.com/JolitaGrazyte)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
