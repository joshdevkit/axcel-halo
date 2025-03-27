<?php

namespace App\Core\Eloquent\Foundations;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;

class Collection implements ArrayAccess, Countable, IteratorAggregate, JsonSerializable
{
    /**
     * The items contained in the collection.
     *
     * @var array
     */
    protected $items = [];

    /**
     * Create a new collection.
     *
     * @param  array  $items
     * @return void
     */
    public function __construct(array $items = [])
    {
        $this->items = $items;
    }

    /**
     * Create a new collection instance if the value isn't one already.
     *
     * @param  mixed  $items
     * @return static
     */
    public static function make($items = [])
    {
        return new static($items);
    }

    /**
     * Get all of the items in the collection.
     *
     * @return array
     */
    public function all()
    {
        return $this->items;
    }

    /**
     * Count the number of items in the collection.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->items);
    }

    /**
     * Get an iterator for the items.
     *
     * @return \ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->items);
    }

    /**
     * Determine if an item exists at an offset.
     *
     * @param  mixed  $key
     * @return bool
     */
    public function offsetExists($key): bool
    {
        return isset($this->items[$key]);
    }

    /**
     * Get an item at a given offset.
     *
     * @param  mixed  $key
     * @return mixed
     */
    public function offsetGet($key): mixed
    {
        return $this->items[$key];
    }

    /**
     * Set the item at a given offset.
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function offsetSet($key, $value): void
    {
        if (is_null($key)) {
            $this->items[] = $value;
        } else {
            $this->items[$key] = $value;
        }
    }

    /**
     * Unset the item at a given offset.
     *
     * @param  mixed  $key
     * @return void
     */
    public function offsetUnset($key): void
    {
        unset($this->items[$key]);
    }

    /**
     * Convert the collection to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    /**
     * Convert the object into something JSON serializable.
     *
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->items;
    }

    /**
     * Convert the collection to JSON.
     *
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->items, $options);
    }

    /**
     * Map the collection using a callback.
     *
     * @param  callable  $callback
     * @return static
     */
    public function map(callable $callback)
    {
        $result = [];

        foreach ($this->items as $key => $value) {
            $result[$key] = $callback($value, $key);
        }

        return new static($result);
    }


    /**
     * Apply a callback to each item in the collection.
     *
     * @param  callable  $callback
     * @return $this
     */
    public function each(callable $callback)
    {
        foreach ($this->items as $key => $item) {
            $callback($item, $key);
        }
        return $this;
    }

    /**
     * Filter items by the given callback.
     *
     * @param  callable|null  $callback
     * @return static
     */
    public function filter(?callable $callback = null)
    {
        if ($callback) {
            $result = [];

            foreach ($this->items as $key => $value) {
                if ($callback($value, $key)) {
                    $result[$key] = $value;
                }
            }

            return new static($result);
        }

        return new static(array_filter($this->items));
    }

    /**
     * Reduce the collection to a single value.
     *
     * @param  callable  $callback
     * @param  mixed  $initial
     * @return mixed
     */
    public function reduce(callable $callback, $initial = null)
    {
        $result = $initial;

        foreach ($this->items as $key => $value) {
            $result = $callback($result, $value, $key);
        }

        return $result;
    }

    /**
     * Get the first item from the collection.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function first(?callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $default;
            }

            foreach ($this->items as $item) {
                return $item;
            }
        }

        foreach ($this->items as $key => $value) {
            if ($callback($value, $key)) {
                return $value;
            }
        }
        return $default;
    }



    /**
     * Get the last item from the collection.
     *
     * @param  callable|null  $callback
     * @param  mixed  $default
     * @return mixed
     */
    public function last(?callable $callback = null, $default = null)
    {
        if (is_null($callback)) {
            if (empty($this->items)) {
                return $default;
            }

            return end($this->items);
        }

        return $this->first($callback, $default);
    }

    /**
     * Get the values of a given key.
     *
     * @param  string|array  $value
     * @return static
     */
    public function pluck($value)
    {
        $results = [];

        foreach ($this->items as $item) {
            $results[] = is_object($item) ? $item->{$value} : $item[$value];
        }

        return new static($results);
    }

    /**
     * Get an array with the values of a given key as keys and another key as values.
     *
     * @param  string  $keyBy
     * @param  string  $value
     * @return static
     */
    public function keyBy($keyBy, $value = null)
    {
        $results = [];

        foreach ($this->items as $item) {
            $key = is_object($item) ? $item->{$keyBy} : $item[$keyBy];

            if ($value) {
                $val = is_object($item) ? $item->{$value} : $item[$value];
                $results[$key] = $val;
            } else {
                $results[$key] = $item;
            }
        }

        return new static($results);
    }


    public function toArray()
    {
        return array_map(function ($item) {
            return method_exists($item, 'toArray') ? $item->toArray() : (array) $item;
        }, $this->items);
    }
}
