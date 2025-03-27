<?php

namespace App\Core\Http\Concerns;

trait InteractsWithInputBag
{
    public function all()
    {
        return $this->request->all();
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return parent::get($key, $default);
    }

    public function only(array $keys)
    {
        return array_filter($this->request->all(), function ($key) use ($keys) {
            return in_array($key, $keys);
        }, ARRAY_FILTER_USE_KEY);
    }

    public function has($key)
    {
        return $this->request->has($key);
    }

    public function except(array|string $keys): array
    {
        if (!is_array($keys)) {
            $keys = [$keys]; // Convert string to array if needed
        }

        return array_diff_key($this->request->all(), array_flip($keys));
    }
}
