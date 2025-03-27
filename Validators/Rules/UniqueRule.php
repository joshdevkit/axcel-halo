<?php

namespace Axcel\AxcelCore\Validators\Rules;

use Axcel\Core\Attributes\Str;
use Axcel\Core\BaseModel;

class UniqueRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        [$table, $column, $ignoreId] = explode(',', $ruleValue . ',,');

        // Handle the case where we ignore the ID from the authenticated user.
        if ($ignoreId === 'auth') {
            $ignoreId = auth()->user()->id;
        }

        // Check if the value already exists in the specified column, ignoring the specified ID if provided.
        if ($this->findColumn($table, $column, $value, $ignoreId)) {
            return ucfirst($field) . " is already taken.";
        }

        return null;
    }

    /**
     * Check if a value exists in the specified table and column, ignoring a specific ID if needed.
     *
     * @param string $table The table name
     * @param string $column The column to check
     * @param mixed $value The value to check
     * @param mixed $ignoreId The ID to ignore
     * @return bool
     */
    protected function findColumn($table, $column, $value, $ignoreId = null)
    {
        $model = $this->resolveModelForTable($table);
        $query = $model::where($column, $value);

        if ($ignoreId !== null) {
            $instance = new $model;
            $query->where($instance->getPrimaryKey(), '!=', $ignoreId);
        }

        return $query->first() !== null;
    }


    /**
     * Resolve the model class for the given table.
     *
     * @param string $table The table name
     * @return string The model class name
     * @throws \Exception If the model cannot be found
     */
    protected function resolveModelForTable($table)
    {
        $singularTable = Str::singular($table);
        $ModelClass = ucfirst($singularTable);
        if (class_exists("\\App\\Models\\$ModelClass")) {
            return "\\App\\Models\\$ModelClass";
        }

        throw new \Exception("Model for table $table not found.");
    }
}
