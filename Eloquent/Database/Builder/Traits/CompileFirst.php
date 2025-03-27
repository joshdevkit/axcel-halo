<?php

namespace App\Core\Eloquent\Database\Builder\Traits;

use Exception;

trait CompileFirst
{
    /**
     * Execute the query and get the first result.
     *
     * @param array $columns
     * @return \App\Core\Eloquent\Foundations\Model|null
     */
    public function first($columns = ['*'])
    {
        $this->limit(1);

        if ($columns !== ['*']) {
            $this->select($columns);
        }

        $results = $this->get();

        return count($results) > 0 ? $results[0] : null;
    }

    /**
     * Execute the query and get the first result or throw an exception.
     *
     * @param array $columns
     * @return \App\Core\Eloquent\Foundations\Model
     * @throws \Exception
     */
    public function firstOrFail($columns = ['*'])
    {
        $result = $this->first($columns);

        if (!$result) {
            throw new Exception('No query results for model [' . get_class($this->model) . '].');
        }

        return $result;
    }


    /**
     * Get the first record matching the attributes or create it.
     *
     * @param array $attributes
     * @param array $values
     * @return \App\Core\Eloquent\Foundations\Model
     */
    public function firstOrCreate(array $attributes, array $values = [])
    {
        $query = clone $this;

        foreach ($attributes as $key => $value) {
            $query->where($key, '=', $value);
        }

        $instance = $query->first();

        if ($instance) {
            return $instance;
        }

        $newAttributes = array_merge($attributes, $values);

        return $this->create($newAttributes);
    }
}
