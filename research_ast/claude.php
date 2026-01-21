<?php

namespace Spatie\Valuestore;

use Countable;
use ArrayAccess;

class Valuestore implements ArrayAccess, Countable
{
    protected string $fileName; // PHP 8.1: Typed property for type safety

    /**
     * @param string $fileName
     * @param array|null $values
     *
     * @return static
     */
    public static function make(string $fileName, array $values = null): static // PHP 8.1: Static return type for fluent interface
    {
        $valuestore = (new static())->setFileName($fileName);

        if (! is_null($values)) {
            $valuestore->put($values);
        }

        return $valuestore;
    }

    protected function __construct()
    {
    }

    /**
     * Set the filename where all values will be stored.
     *
     * @param string $fileName
     *
     * @return static
     */
    protected function setFileName(string $fileName): static // PHP 8.1: Static return type for fluent interface
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Put a value in the store.
     *
     * @param string|array    $name
     * @param mixed $value
     *
     * @return static
     */
    public function put(string|array $name, mixed $value = null): static // PHP 8.1: Union type and mixed type, static return type
    {
        if ($name == []) {
            return $this;
        }

        $newValues = $name;

        if (! is_array($name)) {
            $newValues = [$name => $value];
        }

        $newContent = array_merge($this->all(), $newValues);

        $this->setContent($newContent);

        return $this;
    }

    /**
     * Push a new value into an array.
     *
     * @param string $name
     * @param mixed $pushValue
     *
     * @return static
     */
    public function push(string $name, mixed $pushValue): static // PHP 8.1: Mixed type and static return type
    {
        if (! is_array($pushValue)) {
            $pushValue = [$pushValue];
        }

        if (! $this->has($name)) {
            $this->put($name, $pushValue);

            return $this;
        }

        $oldValue = $this->get($name);

        if (! is_array($oldValue)) {
            $oldValue = [$oldValue];
        }

        $newValue = array_merge($oldValue, $pushValue);

        $this->put($name, $newValue);

        return $this;
    }

    /**
     * Prepend a new value in an array.
     *
     * @param string $name
     * @param mixed $prependValue
     *
     * @return static
     */
    public function prepend(string $name, mixed $prependValue): static // PHP 8.1: Mixed type and static return type
    {
        if (! is_array($prependValue)) {
            $prependValue = [$prependValue];
        }

        if (! $this->has($name)) {
            $this->put($name, $prependValue);

            return $this;
        }

        $oldValue = $this->get($name);

        if (! is_array($oldValue)) {
            $oldValue = [$oldValue];
        }

        $newValue = array_merge($prependValue, $oldValue);

        $this->put($name, $newValue);

        return $this;
    }

    /**
     * Get a value from the store.
     *
     * @param string $name
     * @param mixed $default
     *
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed // PHP 8.1: Mixed type for flexible return value
    {
        $all = $this->all();

        if (! array_key_exists($name, $all)) {
            return $default;
        }

        return $all[$name];
    }

    /*
     * Determine if the store has a value for the given name.
     */
    public function has(string $name) : bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * Get all values from the store.
     *
     * @return array
     */
    public function all() : array
    {
        if (! file_exists($this->fileName)) {
            return [];
        }

        return json_decode(file_get_contents($this->fileName), true) ?? [];
    }

    /**
     * Get all keys starting with a given string from the store.
     *
     * @param string $startingWith
     *
     * @return array
     */
    public function allStartingWith(string $startingWith = '') : array
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
     * @param string $key
     *
     * @return static
     */
    public function forget(string $key): static // PHP 8.1: Static return type for fluent interface
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
    public function flush(): static // PHP 8.1: Static return type for fluent interface
    {
        return $this->setContent([]);
    }

    /**
     * Flush all values which keys start with a given string.
     *
     * @param string $startingWith
     *
     * @return static
     */
    public function flushStartingWith(string $startingWith = ''): static // PHP 8.1: Static return type for fluent interface
    {
        $newContent = [];

        if ($startingWith !== '') {
            $newContent = $this->filterKeysNotStartingWith($this->all(), $startingWith);
        }

        return $this->setContent($newContent);
    }

    /**
     * Get and forget a value from the store.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function pull(string $name): mixed // PHP 8.1: Mixed type for flexible return value
    {
        $value = $this->get($name);

        $this->forget($name);

        return $value;
    }

    /**
     * Increment a value from the store.
     *
     * @param string $name
     * @param int    $by
     *
     * @return int|float
     */
    public function increment(string $name, int $by = 1): int|float // PHP 8.1: Union type for numeric operations type safety
    {
        $currentValue = $this->get($name) ?? 0; // PHP 8.1: Ensure numeric default for safe arithmetic

        $newValue = $currentValue + $by; // PHP 8.1: Type-safe numeric operation

        $this->put($name, $newValue);

        return $newValue;
    }

    /**
     * Decrement a value from the store.
     *
     * @param string $name
     * @param int    $by
     *
     * @return int|float
     */
    public function decrement(string $name, int $by = 1): int|float // PHP 8.1: Union type for numeric operations type safety
    {
        return $this->increment($name, $by * -1);
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool // PHP 8.1: Interface compliance requires mixed type and return type
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed // PHP 8.1: Interface compliance requires mixed type and return type
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void // PHP 8.1: Interface compliance requires mixed type and void return
    {
        $this->put($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void // PHP 8.1: Interface compliance requires mixed type and void return
    {
        $this->forget($offset);
    }

    /**
     * Count elements.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int
     */
    public function count(): int // PHP 8.1: Interface compliance requires return type
    {
        return count($this->all());
    }

    protected function filterKeysStartingWith(array $values, string $startsWith) : array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return str_starts_with($key, $startsWith); // PHP 8.1: Native str_starts_with replaces custom implementation
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function filterKeysNotStartingWith(array $values, string $startsWith) : array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return ! str_starts_with($key, $startsWith); // PHP 8.1: Native str_starts_with replaces custom implementation
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array $values
     *
     * @return static
     */
    protected function setContent(array $values): static // PHP 8.1: Static return type for fluent interface
    {
        file_put_contents($this->fileName, json_encode($values));

        if (! count($values)) {
            unlink($this->fileName);
        }

        return $this;
    }
}
