<?php

declare(strict_types=1);

namespace Spatie\Valuestore;

use Countable;
use ArrayAccess;

/**
 * Modern PHP 8.5 Value Store Implementation
 * 
 * A simple key-value store backed by JSON files with full type safety.
 * 
 * @implements ArrayAccess<string, mixed>
 */
class Valuestore implements ArrayAccess, Countable
{
    /**
     * The file path where all values are stored.
     */
    protected string $fileName;

    /**
     * Create a new Valuestore instance with optional initial values.
     *
     * @param string $fileName The path to the JSON storage file
     * @param array<string, mixed>|null $values Initial values to store
     * @return static
     */
    public static function make(string $fileName, ?array $values = null): static
    {
        /** @var static $valuestore */
        $valuestore = (new static())->setFileName($fileName);

        if ($values !== null) {
            $valuestore->put($values);
        }

        return $valuestore;
    }

    /**
     * Protected constructor to enforce factory pattern usage.
     */
    protected function __construct()
    {
    }

    /**
     * Set the filename where all values will be stored.
     *
     * @param string $fileName The path to the storage file
     * @return static
     */
    protected function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Put a value or multiple values in the store.
     *
     * @param string|array<string, mixed> $name The key name or array of key-value pairs
     * @param mixed $value The value to store (ignored if $name is an array)
     * @return static
     */
    public function put(string|array $name, mixed $value = null): static
    {
        // Skip if empty array is provided
        if ($name === []) {
            return $this;
        }

        $newValues = is_array($name) ? $name : [$name => $value];

        $newContent = array_merge($this->all(), $newValues);

        $this->setContent($newContent);

        return $this;
    }

    /**
     * Push a new value into an array stored at the given key.
     *
     * @param string $name The key name
     * @param mixed $pushValue The value(s) to push
     * @return static
     */
    public function push(string $name, mixed $pushValue): static
    {
        $pushValue = is_array($pushValue) ? $pushValue : [$pushValue];

        if (!$this->has($name)) {
            $this->put($name, $pushValue);
            return $this;
        }

        $oldValue = $this->get($name);
        $oldValue = is_array($oldValue) ? $oldValue : [$oldValue];

        $newValue = array_merge($oldValue, $pushValue);

        $this->put($name, $newValue);

        return $this;
    }

    /**
     * Prepend a new value to an array stored at the given key.
     *
     * @param string $name The key name
     * @param mixed $prependValue The value(s) to prepend
     * @return static
     */
    public function prepend(string $name, mixed $prependValue): static
    {
        $prependValue = is_array($prependValue) ? $prependValue : [$prependValue];

        if (!$this->has($name)) {
            $this->put($name, $prependValue);
            return $this;
        }

        $oldValue = $this->get($name);
        $oldValue = is_array($oldValue) ? $oldValue : [$oldValue];

        $newValue = array_merge($prependValue, $oldValue);

        $this->put($name, $newValue);

        return $this;
    }

    /**
     * Get a value from the store.
     *
     * @param string $name The key name
     * @param mixed $default The default value to return if key doesn't exist
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
    {
        $all = $this->all();

        if (!array_key_exists($name, $all)) {
            return $default;
        }

        return $all[$name];
    }

    /**
     * Determine if the store has a value for the given name.
     *
     * @param string $name The key name
     * @return bool
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * Get all values from the store.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (!file_exists($this->fileName)) {
            return [];
        }

        $contents = file_get_contents($this->fileName);
        
        if ($contents === false) {
            return [];
        }

        $decoded = json_decode($contents, true);
        
        if (!is_array($decoded)) {
            return [];
        }
        
        // Ensure all keys are strings (PHPStan requirement)
        /** @var array<string, mixed> */
        return $decoded;
    }

    /**
     * Get all keys starting with a given string from the store.
     *
     * @param string $startingWith The prefix to filter by
     * @return array<string, mixed>
     */
    public function allStartingWith(string $startingWith = ''): array
    {
        $values = $this->all();

        if ($startingWith === '') {
            return $values;
        }

        return $this->filterKeysStartingWith($values, $startingWith);
    }

    /**
     * Forget a value from the store.
     *
     * @param string $key The key to remove
     * @return static
     */
    public function forget(string $key): static
    {
        $newContent = $this->all();

        unset($newContent[$key]);

        $this->setContent($newContent);

        return $this;
    }

    /**
     * Flush all values from the store.
     *
     * @return static
     */
    public function flush(): static
    {
        return $this->setContent([]);
    }

    /**
     * Flush all values which keys start with a given string.
     *
     * @param string $startingWith The prefix to filter by
     * @return static
     */
    public function flushStartingWith(string $startingWith = ''): static
    {
        $newContent = $startingWith !== '' 
            ? $this->filterKeysNotStartingWith($this->all(), $startingWith)
            : [];

        return $this->setContent($newContent);
    }

    /**
     * Get and forget a value from the store.
     *
     * @param string $name The key name
     * @return mixed
     */
    public function pull(string $name): mixed
    {
        $value = $this->get($name);

        $this->forget($name);

        return $value;
    }

    /**
     * Increment a numeric value in the store.
     *
     * @param string $name The key name
     * @param int $by The amount to increment by
     * @return int
     */
    public function increment(string $name, int $by = 1): int
    {
        $currentValue = $this->get($name);
        
        if (!is_int($currentValue) && !is_float($currentValue)) {
            $currentValue = 0;
        }

        $newValue = (int)($currentValue + $by);

        $this->put($name, $newValue);

        return $newValue;
    }

    /**
     * Decrement a numeric value in the store.
     *
     * @param string $name The key name
     * @param int $by The amount to decrement by
     * @return int
     */
    public function decrement(string $name, int $by = 1): int
    {
        return $this->increment($name, $by * -1);
    }

    /**
     * Whether a offset exists.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset The offset to check
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return is_string($offset) && $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset The offset to retrieve
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        if (!is_string($offset)) {
            return null;
        }
        
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset The offset to assign the value to
     * @param mixed $value The value to set
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (is_string($offset)) {
            $this->put($offset, $value);
        }
    }

    /**
     * Offset to unset.
     *
     * @link https://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset The offset to unset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if (is_string($offset)) {
            $this->forget($offset);
        }
    }

    /**
     * Count elements in the store.
     *
     * @link https://php.net/manual/en/countable.count.php
     * @return int
     */
    public function count(): int
    {
        return count($this->all());
    }

    /**
     * Filter array keys starting with a given string.
     *
     * @param array<string, mixed> $values The array to filter
     * @param string $startsWith The prefix to filter by
     * @return array<string, mixed>
     */
    protected function filterKeysStartingWith(array $values, string $startsWith): array
    {
        return array_filter(
            $values,
            fn(string $key): bool => str_starts_with($key, $startsWith),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Filter array keys NOT starting with a given string.
     *
     * @param array<string, mixed> $values The array to filter
     * @param string $startsWith The prefix to filter by
     * @return array<string, mixed>
     */
    protected function filterKeysNotStartingWith(array $values, string $startsWith): array
    {
        return array_filter(
            $values,
            fn(string $key): bool => !str_starts_with($key, $startsWith),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * Set the content of the store and persist to disk.
     *
     * @param array<string, mixed> $values The values to store
     * @return static
     */
    protected function setContent(array $values): static
    {
        file_put_contents($this->fileName, json_encode($values));

        if (count($values) === 0) {
            unlink($this->fileName);
        }

        return $this;
    }
}
