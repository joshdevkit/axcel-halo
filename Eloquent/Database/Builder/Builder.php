<?php

namespace Axcel\AxcelCore\Eloquent\Database\Builder;

use Axcel\AxcelCore\Eloquent\Database\Builder\Traits\CompileFirst;
use Axcel\AxcelCore\Eloquent\Database\Builder\Traits\Compiler;
use Axcel\AxcelCore\Eloquent\Database\Builder\Traits\CompileWheres;
use Axcel\AxcelCore\Eloquent\Database\Builder\Traits\Counting;
use Axcel\AxcelCore\Eloquent\Database\Builder\Traits\Orders;
use Axcel\AxcelCore\Eloquent\Foundations\Model;
use Axcel\AxcelCore\Eloquent\Database\ConnectionManager;
use Axcel\AxcelCore\Eloquent\Foundations\Collection;
use PDO;
use Exception;

class Builder
{
    /**
     * Compiler Traits
     */
    use Compiler,
        Orders,
        Counting,
        CompileFirst,
        CompileWheres;
    /**
     * The model being queried.
     *
     * @var \Axcel\AxcelCore\Eloquent\Foundations\Model
     */
    protected $model;

    /**
     * The database connection.
     *
     * @var \PDO
     */
    protected $connection;

    /**
     * The database connection name.
     *
     * @var string
     */
    protected $connectionName = 'default';

    /**
     * The columns that should be returned.
     *
     * @var array
     */
    protected $columns = ['*'];

    /**
     * The table which the query is targeting.
     *
     * @var string
     */
    protected $table;

    /**
     * The where constraints for the query.
     *
     * @var array
     */
    protected $wheres = [];

    /**
     * The orderings for the query.
     *
     * @var array
     */
    protected $orders = [];

    /**
     * The maximum number of records to return.
     *
     * @var int
     */
    protected $limit;

    /**
     * The number of records to skip.
     *
     * @var int
     */
    protected $offset;

    /**
     * All of the available clause operators.
     *
     * @var array
     */
    protected $operators = [
        '=',
        '<',
        '>',
        '<=',
        '>=',
        '<>',
        '!=',
        'like',
        'not like',
        'in',
        'not in',
        'is',
        'is not'
    ];

    /**
     * The current query value bindings.
     *
     * @var array
     */
    protected $bindings = [
        'where' => [],
        'select' => [],
        'join' => [],
        'order' => [],
    ];

    /**
     * The relationships that should be eager loaded.
     *
     * @var array
     */
    public $eagerLoad = [];

    /**
     * Set the model instance for the builder.
     *
     * @param \Axcel\AxcelCore\Eloquent\Foundations\Model $model
     * @return $this
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
        $this->table = $model->getTable();
        return $this;
    }


    /**
     * Set the connection name.
     *
     * @param string $name
     * @return $this
     */
    public function setConnection(string $name)
    {
        $this->connectionName = $name;
        $this->connection = ConnectionManager::getPdo($name);
        return $this;
    }

    /**
     * Get the current PDO connection.
     *
     * @return \PDO
     */
    public function getConnection()
    {
        if (!$this->connection) {
            $this->connection = ConnectionManager::getPdo($this->connectionName);
        }
        return $this->connection;
    }


    public function getBindings()
    {
        return $this->bindings;
    }

    public function getTable()
    {
        return $this->model->getTable();
    }

    public function getEagerLoad()
    {
        return $this->eagerLoad;
    }


    /**
     * Set the columns to be selected.
     *
     * @param array|string $columns
     * @return $this
     */
    public function select($columns = ['*'])
    {
        $this->columns = is_array($columns) ? $columns : func_get_args();
        return $this;
    }

    /**
     * Add a JOIN clause to the query.
     *
     * @param string $table The table to join.
     * @param string $first The first column in the join condition.
     * @param string $operator The operator for the join condition.
     * @param string $second The second column in the join condition.
     * @param string $type The type of join (default: "INNER").
     * @return $this
     */
    public function join($table, $first, $operator, $second, $type = 'INNER')
    {
        $this->bindings['join'][] = compact('table', 'first', 'operator', 'second', 'type');
        return $this;
    }


    /**
     * Get the SQL representation of the query.
     *
     * @return string
     */
    public function toSql()
    {
        $sql = $this->compileSelect();

        // Replace ? with the actual values for easy debugging
        $bindings = array_merge(
            $this->bindings['select'],
            $this->bindings['where'],
            $this->bindings['join'],
            $this->bindings['order']
        );

        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'{$binding}'";
            $pos = strpos($sql, '?');
            if ($pos !== false) {
                $sql = substr_replace($sql, $value, $pos, 1);
            }
        }

        return $sql;
    }



    /**
     * Find a model by its primary key.
     *
     * @param mixed $id
     * @param array $columns
     * @return \Axcel\AxcelCore\Eloquent\Foundations\Model|null
     */
    public function find($id, $columns = ['*'])
    {
        if (is_array($id)) {
            return $this->whereIn($this->model->getPrimaryKey(), $id)->get($columns);
        }

        return $this->where($this->model->getPrimaryKey(), '=', $id)->first($columns);
    }

    /**
     * Find a model by its primary key or throw an exception.
     *
     * @param mixed $id
     * @param array $columns
     * @return \Axcel\AxcelCore\Eloquent\Foundations\Model
     * @throws \Exception
     */
    public function findOrFail($id, $columns = ['*'])
    {
        $result = $this->find($id, $columns);

        if (!$result) {
            throw new Exception('No query results for model [' . get_class($this->model) . '] ' . $id);
        }

        return $result;
    }

    /**
     * Execute the query and get the results.
     *
     * @param array $columns
     * @return \Axcel\AxcelCore\Eloquent\Foundations\Collection
     */
    public function get($columns = ['*'])
    {
        if ($columns !== ['*']) {
            $this->select($columns);
        }

        $statement = $this->prepareStatement();
        $statement->execute();

        $results = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $model = clone $this->model;
            $model->setRawAttributes($row);
            $results[] = $model;
        }

        $collection = new Collection($results);

        return $this->eagerLoadRelations($collection);
    }


    protected function eagerLoadRelations(Collection $models)
    {
        foreach ($this->getEagerLoad() as $relation) {
            $models->each(function ($model) use ($relation) {
                if (method_exists($model, $relation)) {
                    // Get the relation query
                    $relationQuery = $model->$relation();
                    if ($relationQuery instanceof \Axcel\AxcelCore\Eloquent\Database\Builder\Builder) {
                        // dd("hasmany method called! true");
                        $relationData = $relationQuery->get();
                        $model->setRelation($relation, $relationData);
                    } else {
                        // dd("hasmany method called! false");
                        $relationData = $relationQuery->limit(1)->first();
                        $model->setRelation($relation, $relationData);
                    }
                }
            });
        }

        return $models;
    }


    /**
     * Get all of the models from the database.
     *
     * @return Axcel\AxcelCore\Eloquent\Foundations\Collection
     */
    public function all($columns = ['*'])
    {
        return $this->get($columns);
    }

    /**
     * Get a single column's value from the first result of a query.
     *
     * @param string $column
     * @return mixed
     */
    public function value($column)
    {
        $result = $this->first([$column]);

        return $result ? $result->{$column} : null;
    }

    /**
     * Get an array with the values of a given column.
     *
     * @param string $column
     * @param string|null $key
     * @return array
     */
    public function pluck($column, $key = null)
    {
        $columns = $key ? [$column, $key] : [$column];
        $results = $this->get($columns);

        $values = [];
        foreach ($results as $result) {
            if ($key) {
                $values[$result->{$key}] = $result->{$column};
            } else {
                $values[] = $result->{$column};
            }
        }

        return $values;
    }

    /**
     * Create a new record in the database.
     *
     * @param array $attributes
     * @return \Axcel\AxcelCore\Eloquent\Foundations\Model
     */
    public function create(array $attributes)
    {
        $model = clone $this->model;
        $model->fill($attributes);

        $this->saveModel($model);

        return $model;
    }

    /**
     * Save the model to the database.
     *
     * @param \Axcel\AxcelCore\Eloquent\Foundations\Model $model
     * @return bool
     */
    public function saveModel(Model $model)
    {
        $attributes = $model->getAttributes();
        $primaryKey = $model->getPrimaryKey();

        // Determine if the model exists
        $exists = isset($attributes[$primaryKey]) && !empty($attributes[$primaryKey]);

        if ($exists) {
            $pk = $attributes[$primaryKey];
            unset($attributes[$primaryKey]); // Remove primary key from update
            $result = $this->where($primaryKey, '=', $pk)->update($attributes);
            // Re-add the primary key
            $attributes[$primaryKey] = $pk;
            $model->setRawAttributes($attributes);
            return $result;
        }

        $result = $this->insertGetId($attributes);

        if ($result) {
            $attributes[$primaryKey] = $result;
            $model->setRawAttributes($attributes);
            return true;
        }

        return false;
    }

    /**
     * Insert a new record into the database.
     *
     * @param array $values
     * @return bool
     */
    public function insert(array $values)
    {
        if (empty($values)) {
            return true;
        }

        $columns = array_keys($values);
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") VALUES ({$placeholders})";

        $statement = $this->getConnection()->prepare($sql);

        $bindingValues = array_values($values);

        return $statement->execute($bindingValues);
    }

    /**
     * Insert a new record and get the ID.
     *
     * @param array $values
     * @return int
     */
    public function insertGetId(array $values)
    {
        $this->insert($values);
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Update records in the database.
     *
     * @param array $values
     * @return int
     */
    public function update(array $values)
    {
        if (empty($values)) {
            return 0;
        }

        $sets = [];
        $bindingValues = [];

        foreach ($values as $column => $value) {
            $sets[] = "{$column} = ?";
            $bindingValues[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);

        if (!empty($this->wheres)) {
            $whereClause = $this->compileWheres();
            if (!empty($whereClause)) {
                $sql .= " " . $whereClause;
            }
            $bindingValues = array_merge($bindingValues, $this->bindings['where']);
        } else {
            throw new Exception("Update query missing WHERE condition! Possible mass update.");
        }

        $statement = $this->getConnection()->prepare($sql);
        $statement->execute($bindingValues);

        return $statement->rowCount();
    }


    /**
     * Delete records from the database.
     *
     * @return int
     */
    public function delete()
    {
        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= " " . $this->compileWheres();
        }

        $statement = $this->getConnection()->prepare($sql);

        $statement->execute($this->bindings['where']);

        return $statement->rowCount();
    }


    /**
     * Update or create a record matching the attributes, and fill it with values.
     *
     * @param array $attributes
     * @param array $values
     * @return \Axcel\AxcelCore\Eloquent\Foundations\Model
     */
    public function updateOrCreate(array $attributes, array $values = [])
    {
        $query = clone $this;

        foreach ($attributes as $key => $value) {
            $query->where($key, '=', $value);
        }

        $instance = $query->first();

        if ($instance) {
            $instance->fill($values);
            $query->saveModel($instance);
            return $instance;
        }

        $newAttributes = array_merge($attributes, $values);

        return $this->create($newAttributes);
    }

    public function with($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $relation) {
            if (!method_exists($this->model, $relation)) {
                throw new Exception("The relationship method '{$relation}' does not exist in " . get_class($this->model));
            }
        }

        $this->eagerLoad = $relations;
        return $this;
    }
}
