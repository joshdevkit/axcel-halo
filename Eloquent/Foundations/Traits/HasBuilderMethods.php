<?php

namespace Axcel\AxcelCore\Eloquent\Foundations\Traits;

use Exception;

trait HasBuilderMethods
{


    /**
     * Delete the model from the database.
     *
     * @return bool|null
     */
    protected function delete()
    {
        $key = $this->getPrimaryKey();

        if (isset($this->attributes[$key])) {
            return $this->builder()->where($key, '=', $this->attributes[$key])->delete();
        }

        return false;
    }

    /**
     * Save the model to the database.
     *
     * @return bool
     */
    protected function save()
    {
        return $this->builder()->saveModel($this);
    }


    /**
     * update current model to the database.
     *
     * @return bool
     */
    protected function update(array $attributes)
    {
        if (!$this->exists) {
            throw new Exception("Cannot update a non-existent model.");
        }

        return static::query()->where($this->getPrimaryKey(), $this->getKey())->update($attributes);
    }
}
