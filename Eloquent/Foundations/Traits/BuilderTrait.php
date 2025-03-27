<?php

namespace App\Core\Eloquent\Foundations\Traits;

trait BuilderTrait
{
    protected $pdo;

    // Set the PDO instance from the Builder
    public function setPdo($pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Execute a SELECT query
     */
    public function select($query, array $bindings = [])
    {
        $statement = $this->execute($query, $bindings);
        return $statement->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Execute an INSERT query
     */
    public function push($table, array $values)
    {
        $columns = implode(', ', array_keys($values));
        $placeholders = ':' . implode(', :', array_keys($values));

        $query = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";

        $this->execute($query, $values);
        return $this->lastInsertId();
    }

    /**
     * Execute an UPDATE query
     */
    /**
     * Update a single record by primary key
     * 
     * @param string $table The table name
     * @param array $attributes The attributes to update
     * @param string $condition The WHERE condition
     * @param array $bindings The parameter bindings
     * @return int Number of affected rows
     */
    protected function updateRecord($table, array $attributes, $condition, array $bindings)
    {
        $sql = "UPDATE {$table} SET ";
        $sets = [];
        $updateBindings = [];

        // Build SET clause and bindings
        foreach ($attributes as $column => $value) {
            // Skip the primary key and any null values
            if ($column === $this->model->getPrimaryKey() || $value === null) {
                continue;
            }

            $sets[] = "{$column} = ?";
            $updateBindings[] = $value;
        }

        // Combine SQL parts
        $sql .= implode(', ', $sets);
        $sql .= " WHERE " . $condition;

        // Combine all bindings (update values first, then condition bindings)
        $allBindings = array_merge($updateBindings, array_values($bindings));

        // Execute the query
        return $this->execute($sql, $allBindings);
    }

    /**
     * Execute a DELETE query
     */
    public function destroy($table, array $bindings = [])
    {
        $where = "{$this->model->getPrimaryKey()} = ?";
        $query = "DELETE FROM {$table} WHERE {$where}";
        return $this->execute($query, $bindings)->rowCount();
    }


    /**
     * Execute a raw query
     */
    public function execute($query, array $bindings = [])
    {
        $statement = $this->pdo->prepare($query);

        foreach ($bindings as $key => $value) {
            $parameter = is_string($key) ? ":{$key}" : $key + 1;
            $statement->bindValue($parameter, $value, $this->getDataType($value));
        }

        $statement->execute();
        return $statement;
    }

    /**
     * Get the last inserted ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }

    /**
     * Get the PDO data type for a value
     */
    protected function getDataType($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                return \PDO::PARAM_BOOL;
            case 'integer':
                return \PDO::PARAM_INT;
            case 'NULL':
                return \PDO::PARAM_NULL;
            default:
                return \PDO::PARAM_STR;
        }
    }
}
