<?php

namespace Spatie\Valuestore;

use Countable;
use ArrayAccess;

class Valuestore implements ArrayAccess, Countable
{
    /** @var string */
    protected string $fileName = ''; // PHP 8.1 typed property; initialize to avoid uninitialized property Error

    /**
     * @param string $fileName
     * @param array|null $values
     *
     * @return $this
     */
    public static function make(string $fileName, ?array $values = null): static // PHP 8.1: add nullable param type + static return type for fluent factory
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
     * @return $this
     */
    protected function setFileName(string $fileName): static // PHP 8.1: static return type for fluent interface
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Put a value in the store.
     *
     * @param string|array    $name
     * @param string|int|null $value
     *
     * @return $this
     */
    public function put(string|int|array $name, mixed $value = null): static // PHP 8.1: add union/mixed param types + static return type for fluent interface
    {
        if (is_array($name) && $name === []) { // PHP 8.1: avoid array/non-array loose comparison; keep empty-array no-op behavior
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
     * @param $pushValue
     *
     * @return $this
     */
    public function push(string|int $name, mixed $pushValue): static // PHP 8.1: key can be int via ArrayAccess; add mixed + static return type
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
     * @param $prependValue
     *
     * @return $this
     */
    public function prepend(string|int $name, mixed $prependValue): static // PHP 8.1: key can be int via ArrayAccess; add mixed + static return type
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
     * @param $default
     *
     * @return null|string|array
     */
    public function get(string|int $name, mixed $default = null): mixed // PHP 8.1: align with ArrayAccess keys + use mixed return type
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
    public function has(string|int $name) : bool // PHP 8.1: align with ArrayAccess keys (mixed offsets can be int)
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
        if ($this->fileName === '' || ! file_exists($this->fileName)) { // PHP 8.1: typed property may be empty before initialization; avoid invalid path calls
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
     * @return $this
     */
    public function forget(string|int $key): static // PHP 8.1: align with ArrayAccess keys + static return type for fluent interface
    {
        $newContent = $this->all();

        unset($newContent[$key]);

        $this->setContent($newContent);

        return $this;
    }

    /**
     * Flush all values from the store.
     *
     * @return $this
     */
    public function flush(): static // PHP 8.1: static return type for fluent interface
    {
        return $this->setContent([]);
    }

    /**
     * Flush all values which keys start with a given string.
     *
     * @param string $startingWith
     *
     * @return $this
     */
    public function flushStartingWith(string $startingWith = ''): static // PHP 8.1: static return type for fluent interface
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
     * @return null|string
     */
    public function pull(string|int $name): mixed // PHP 8.1: align with ArrayAccess keys + use mixed return type
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
     * @return int|null|string
     */
    public function increment(string|int $name, int $by = 1): int|float // PHP 8.1: ensure arithmetic is type-safe; result can be int or float
    {
        $currentValue = $this->get($name); // PHP 8.1: fetch raw value first so we can normalize types safely before arithmetic
        $currentValue = $currentValue ?? 0; // PHP 8.1: preserve null-as-0 behavior explicitly
        if (! is_int($currentValue) && ! is_float($currentValue)) { // PHP 8.1: avoid TypeError when adding non-numeric values
            $currentValue = (is_string($currentValue) && is_numeric($currentValue)) ? ($currentValue + 0) : 0; // PHP 8.1: numeric-string to number; otherwise fall back to 0 like PHP 7.x coercion
        }

        $newValue = $currentValue + $by;

        $this->put($name, $newValue);

        return $newValue;
    }

    /**
     * Decrement a value from the store.
     *
     * @param string $name
     * @param int    $by
     *
     * @return int|null|string
     */
    public function decrement(string|int $name, int $by = 1): int|float // PHP 8.1: match increment() return type + safe arithmetic
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
    public function offsetExists(mixed $offset): bool // PHP 8.1: ArrayAccess requires typed signature
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
    public function offsetGet(mixed $offset): mixed // PHP 8.1: ArrayAccess requires typed signature
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
    public function offsetSet(mixed $offset, mixed $value): void // PHP 8.1: ArrayAccess requires void return type
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
    public function offsetUnset(mixed $offset): void // PHP 8.1: ArrayAccess requires void return type
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
    public function count(): int // PHP 8.1: Countable requires int return type
    {
        return count($this->all());
    }

    protected function filterKeysStartingWith(array $values, string $startsWith) : array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return $this->startsWith($key, $startsWith);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function filterKeysNotStartingWith(array $values, string $startsWith) : array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return ! $this->startsWith($key, $startsWith);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function startsWith(string $haystack, string $needle) : bool
    {
        return str_starts_with($haystack, $needle); // PHP 8.1: use native str_starts_with equivalent
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    protected function setContent(array $values): static // PHP 8.1: static return type for fluent interface
    {
        file_put_contents($this->fileName, json_encode($values));

        if (! count($values)) {
            unlink($this->fileName);
        }

        return $this;
    }
}
