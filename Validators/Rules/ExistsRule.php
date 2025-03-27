<?php

namespace App\Core\Validators\Rules;

use Axcel\Core\Attributes\Str;

class ExistsRule
{
    public function validate(string $field, $value, ?string $ruleValue = null, array $data = []): ?string
    {
        // Ensure that the rule value is in the format 'table,column'
        if (!$ruleValue) {
            return "Rule value is required.";
        }

        [$table, $column] = explode(',', $ruleValue);

        // Check if the value exists in the specified table and column
        if (!$this->findColumn($table, $column, $value)) {
            if ($column === "email") {
                return ucfirst($field) . " not found.";
            }

            return ucfirst($field) . " not found.";
        }

        return null;
    }

    /**
     * Check if the value exists in the given table and column.
     *
     * @param string $table The table name
     * @param string $column The column name
     * @param mixed $value The value to check
     * @return bool True if the value exists, false otherwise
     */
    protected function findColumn($table, $column, $value)
    {
        $model = $this->resolveModelForTable($table);

        if (!class_exists($model)) {
            throw new \Exception("Model for table $table not found.");
        }


        $query = $model::where($column, $value);

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
        $modelName = ucfirst($singularTable);

        if (class_exists("\\App\\Models\\$modelName")) {
            return "\\App\\Models\\$modelName";
        }

        $pluralModelName = ucfirst($table);
        if (class_exists("\\App\\Models\\$pluralModelName")) {
            return "\\App\\Models\\$pluralModelName";
        }

        throw new \Exception("Model for table $table not found.");
    }
}
