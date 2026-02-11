<?php

declare(strict_types=1);

namespace Spatie\Valuestore;

use Countable;
use ArrayAccess;
use JsonException;

class Valuestore implements ArrayAccess, Countable
{
    protected string $fileName;

    /**
     * @param string $fileName
     * @param array<string, mixed>|null $values
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

    public function setFileName(string $fileName): static
    {
        $this->fileName = $fileName;

        return $this;
    }

    /**
     * Put a value in the store.
     *
     * @param string|array<string, mixed> $name
     */
    public function put(string|array $name, mixed $value = null): static
    {
        if ($name === []) {
            return $this;
        }

        $newValues = is_array($name) ? $name : [$name => $value];

        $newContent = array_merge($this->all(), $newValues);

        $this->setContent($newContent);

        return $this;
    }

    public function push(string $name, mixed $pushValue): static
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

    public function prepend(string $name, mixed $prependValue): static
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

    public function get(string $name, mixed $default = null): mixed
    {
        $all = $this->all();

        if (! array_key_exists($name, $all)) {
            return $default;
        }

        return $all[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        if (! file_exists($this->fileName)) {
            return [];
        }

        $content = file_get_contents($this->fileName);
        
        if ($content === false) {
            return [];
        }

        try {
            return json_decode($content, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (JsonException) {
            return [];
        }
    }

    /**
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

    public function forget(string $key): static
    {
        $newContent = $this->all();

        unset($newContent[$key]);

        $this->setContent($newContent);

        return $this;
    }

    public function flush(): static
    {
        return $this->setContent([]);
    }

    public function flushStartingWith(string $startingWith = ''): static
    {
        $newContent = [];

        if ($startingWith !== '') {
            $newContent = $this->filterKeysNotStartingWith($this->all(), $startingWith);
        }

        return $this->setContent($newContent);
    }

    public function pull(string $name): mixed
    {
        $value = $this->get($name);

        $this->forget($name);

        return $value;
    }

    public function increment(string $name, int $by = 1): int|float
    {
        $currentValue = $this->get($name) ?? 0;

        if (!is_numeric($currentValue)) {
             $currentValue = 0;
        }

        $newValue = $currentValue + $by;

        $this->put($name, $newValue);

        return $newValue;
    }

    public function decrement(string $name, int $by = 1): int|float
    {
        return $this->increment($name, $by * -1);
    }

    /**
     * Whether a offset exists.
     *
     * @param mixed $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->has((string) $offset);
    }

    /**
     * Offset to retrieve.
     *
     * @param mixed $offset
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get((string) $offset);
    }

    /**
     * Offset to set.
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->put((string) $offset, $value);
    }

    /**
     * Offset to unset.
     *
     * @param mixed $offset
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->forget((string) $offset);
    }

    public function count(): int
    {
        return count($this->all());
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    protected function filterKeysStartingWith(array $values, string $startsWith): array
    {
        return array_filter($values, fn ($key) => str_starts_with((string)$key, $startsWith), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<string, mixed> $values
     * @return array<string, mixed>
     */
    protected function filterKeysNotStartingWith(array $values, string $startsWith): array
    {
        return array_filter($values, fn ($key) => ! str_starts_with((string)$key, $startsWith), ARRAY_FILTER_USE_KEY);
    }

    /**
     * @param array<string, mixed> $values
     */
    protected function setContent(array $values): static
    {
        file_put_contents($this->fileName, json_encode($values, JSON_THROW_ON_ERROR));

        if (count($values) === 0) {
            @unlink($this->fileName);
        }

        return $this;
    }
}
