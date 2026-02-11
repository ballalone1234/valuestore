<?php

namespace Spatie\Valuestore;

use Countable;
use ArrayAccess;

class Valuestore implements ArrayAccess, Countable
{
    protected string $fileName;

    /**
     * สร้าง Valuestore พร้อมกำหนดไฟล์เก็บข้อมูล (และค่าเริ่มต้นถ้ามี)
     */
    public static function make(string $fileName, ?array $values = null): static
    {
        $valuestore = new static($fileName);

        if (! is_null($values)) {
            $valuestore->put($values);
        }

        return $valuestore;
    }

    protected function __construct(string $fileName)
    {
        $this->fileName = $fileName;
    }

    /**
     * Put a value in the store.
     *
     * Legacy compatibility: ต้องรองรับทั้ง put('key', 'value') และ put(['k'=>'v'])
     */
    public function put(string|int|null|array $name, mixed $value = null): static
    {
        // PHP 8+ เปลี่ยนพฤติกรรม loose compare ระหว่าง array กับ scalar
        // เราตั้งใจข้ามเฉพาะกรณี "ส่ง array ว่าง" เท่านั้น (ตามเทสต์)
        if (is_array($name) && $name === []) {
            return $this;
        }

        $newValues = self::normalizePutArguments($name, $value);

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
    public function push(string $name, mixed $pushValue): static
    {
        $pushValue = self::normalizeToArray($pushValue);

        if (! $this->has($name)) {
            $this->put($name, $pushValue);

            return $this;
        }

        $oldValue = $this->get($name);

        $oldValue = self::normalizeToArray($oldValue);

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
    public function prepend(string $name, mixed $prependValue): static
    {
        $prependValue = self::normalizeToArray($prependValue);

        if (! $this->has($name)) {
            $this->put($name, $prependValue);

            return $this;
        }

        $oldValue = $this->get($name);

        $oldValue = self::normalizeToArray($oldValue);

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
     * @return mixed
     */
    public function get(string $name, mixed $default = null): mixed
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
    public function has(string $name): bool
    {
        return array_key_exists($name, $this->all());
    }

    /**
     * Get all values from the store.
     *
     * @return array
     */
    public function all(): array
    {
        if (! file_exists($this->fileName)) {
            return [];
        }

        $contents = file_get_contents($this->fileName);

        if ($contents === false || $contents === '') {
            return [];
        }

        return json_decode($contents, true) ?? [];
    }

    /**
     * Get all keys starting with a given string from the store.
     *
     * @param string $startingWith
     *
     * @return array
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
     * @param string $key
     *
     * @return $this
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
     * @return $this
     */
    public function flush(): static
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
     *
     * @param string $name
     *
     * @return mixed
     */
    public function pull(string $name): mixed
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
    public function increment(string $name, int $by = 1): int|float
    {
        // Legacy compatibility: PHP 7 อนุโลมกว่าในการ + กับ string/ค่าแปลก ๆ
        // PHP 8+ อาจ TypeError ได้ เราจึง coerce ให้เป็นเลขก่อน
        $currentValue = $this->get($name);
        $numericCurrent = self::coerceToNumeric($currentValue);
        $newValue = $numericCurrent + $by;

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
    public function decrement(string $name, int $by = 1): int|float
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
    public function offsetExists(mixed $offset): bool
    {
        if ($offset === null) {
            return false;
        }

        return $this->has((string) $offset);
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
    public function offsetGet(mixed $offset): mixed
    {
        if ($offset === null) {
            return null;
        }

        return $this->get((string) $offset);
    }

    /**
     * Offset to set.
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet(mixed $offset, mixed $value): void
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
    public function offsetUnset(mixed $offset): void
    {
        if ($offset === null) {
            return;
        }

        $this->forget((string) $offset);
    }

    /**
     * Count elements.
     *
     * @link http://php.net/manual/en/countable.count.php
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->all());
    }

    protected function filterKeysStartingWith(array $values, string $startsWith): array
    {
        return array_filter(
            $values,
            fn ($key) => str_starts_with((string) $key, $startsWith),
            ARRAY_FILTER_USE_KEY
        );
    }

    protected function filterKeysNotStartingWith(array $values, string $startsWith): array
    {
        return array_filter(
            $values,
            fn ($key) => ! str_starts_with((string) $key, $startsWith),
            ARRAY_FILTER_USE_KEY
        );
    }

    /**
     * @param array $values
     *
     * @return $this
     */
    protected function setContent(array $values): static
    {
        file_put_contents($this->fileName, json_encode($values));

        if (! count($values)) {
            if (file_exists($this->fileName)) {
                unlink($this->fileName);
            }
        }

        return $this;
    }

    // ---------------------------------------------------------------------
    // Legacy PHP 7 behavior clustering (normalize/coerce) vs Modern PHP 8.5 types
    // ---------------------------------------------------------------------

    private static function normalizePutArguments(string|int|null|array $name, mixed $value): array
    {
        if (is_array($name)) {
            return $name;
        }

        return [$name => $value];
    }

    private static function normalizeToArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        return [$value];
    }

    private static function coerceToNumeric(mixed $value): int|float
    {
        if (is_int($value) || is_float($value)) {
            return $value;
        }

        if (is_string($value) && is_numeric($value)) {
            // ถ้าเป็นเลขทศนิยม/วิทยาศาสตร์ให้เก็บเป็น float เพื่อไม่ทำให้ข้อมูลเพี้ยน
            $lower = strtolower($value);
            if (str_contains($value, '.') || str_contains($lower, 'e')) {
                return (float) $value;
            }

            return (int) $value;
        }

        return 0;
    }
}
