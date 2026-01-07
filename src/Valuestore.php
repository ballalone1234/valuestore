<?php

namespace Spatie\Valuestore;

use Countable;
use ArrayAccess;

class Valuestore implements ArrayAccess, Countable
{
    protected string $fileName;

    /**
     * Create a new Valuestore instance.
     */
    public static function make(string $fileName, ?array $values = null): static
    {
        $valuestore = (new static())->setFileName($fileName);

        if ($values !== null) {
            $valuestore->put($values);
        }

        return $valuestore;
    }

    protected function __construct()
    {
    }

    /**
     * Set the filename where all values will be stored.
     */
    protected function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Put a value in the store.
     */
    public function put(string|array $name, mixed $value = null): static
    {
        if ($name === []) {
            return $this;
        }

        $newValues = $name;

        if (!is_array($name)) {
            $newValues = [$name => $value];
        }

        $newContent = array_merge($this->all(), $newValues);

        $this->setContent($newContent);

        return $this;
    }

    /**
     * Push a new value into an array.
     */
    public function push(string $name, mixed $pushValue): static
    {
        if (!is_array($pushValue)) {
            $pushValue = [$pushValue];
        }

        if (!$this->has($name)) {
            $this->put($name, $pushValue);

            return $this;
        }

        $oldValue = $this->get($name);

        if (!is_array($oldValue)) {
            $oldValue = [$oldValue];
        }

        $newValue = array_merge($oldValue, $pushValue);

        $this->put($name, $newValue);

        return $this;
    }

    /**
     * Prepend a new value in an array.
     */
    public function prepend(string $name, mixed $prependValue): static
    {
        if (!is_array($prependValue)) {
            $prependValue = [$prependValue];
        }

        if (!$this->has($name)) {
            $this->put($name, $prependValue);

            return $this;
        }

        $oldValue = $this->get($name);

        if (!is_array($oldValue)) {
            $oldValue = [$oldValue];
        }

        $newValue = array_merge($prependValue, $oldValue);

        $this->put($name, $newValue);

        return $this;
    }

    /**
     * Get a value from the store.
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
     */
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * Get all values from the store.
     */
    public function all(): array
    {
        if (!file_exists($this->fileName)) {
            return [];
        }

        return json_decode(file_get_contents($this->fileName), true) ?? [];
    }

    /**
     * Get all keys starting with a given string from the store.
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
     */
    public function flush(): static
    {
        return $this->setContent([]);
    }

    /**
     * Flush all values which keys start with a given string.
     */
    public function flushStartingWith(string $startingWith = ''): static
    {
        $newContent = [];

        if ($startingWith !== '') {
            $newContent = $this->filterKeysNotStartingWith($this->all(), $startingWith);
        }

        return $this->setContent($newContent);
    }

    /**
     * Get and forget a value from the store.
     */
    public function pull(string $name): mixed
    {
        $value = $this->get($name);

        $this->forget($name);

        return $value;
    }

    /**
     * Increment a value from the store.
     */
    public function increment(string $name, int $by = 1): int
    {
        $currentValue = $this->get($name) ?? 0;

        $newValue = $currentValue + $by;

        $this->put($name, $newValue);

        return $newValue;
    }

    /**
     * Decrement a value from the store.
     */
    public function decrement(string $name, int $by = 1): int
    {
        return $this->increment($name, $by * -1);
    }

    /**
     * Whether a offset exists.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->put($offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->forget($offset);
    }

    /**
     * Count elements.
     *
     * @link http://php.net/manual/en/countable.count.php
     */
    public function count(): int
    {
        return count($this->all());
    }

    protected function filterKeysStartingWith(array $values, string $startsWith): array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return $this->startsWith($key, $startsWith);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function filterKeysNotStartingWith(array $values, string $startsWith): array
    {
        return array_filter($values, function ($key) use ($startsWith) {
            return !$this->startsWith($key, $startsWith);
        }, ARRAY_FILTER_USE_KEY);
    }

    protected function startsWith(string $haystack, string $needle): bool
    {
        return str_starts_with($haystack, $needle);
    }

    /**
     * Set the content of the store.
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
