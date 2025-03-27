<?php

namespace Axcel\AxcelCore\Eloquent\Database\Builder\Traits;

use Exception;

trait CompileWheres
{
    /**
     * Add a where condition to the query.
     *
     * @param  string  $column
     * @param  string  $operator
     * @param  mixed  $value
     * @param  string  $boolean
     * @return $this
     */
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {

        if ($column instanceof \Closure) {
            return $this->whereNested($column, $boolean);
        }

        // If only two arguments are provided, assume equals operator
        if (func_num_args() === 2) {
            list($value, $operator) = [$operator, '='];
        }

        // Check if the operator is valid
        if (!in_array(strtolower($operator), $this->operators)) {
            throw new Exception("Invalid operator: {$operator}");
        }

        // Handle "in" operator
        if (strtolower($operator) === 'in' && is_array($value)) {
            return $this->whereIn($column, $value, $boolean);
        }

        // Handle other operators
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        $this->bindings['where'][] = $value;

        return $this;
    }

    /**
     * Add an "or where" clause to the query.
     *
     * @param string $column
     * @param string $operator
     * @param mixed $value
     * @return $this
     */
    public function orWhere($column, $operator = null, $value = null)
    {
        return $this->where($column, $operator, $value, 'or');
    }

    /**
     * Add a where in clause to the query.
     *
     * @param string $column
     * @param array $values
     * @param string $boolean
     * @return $this
     */
    public function whereIn($column, array $values, $boolean = 'and')
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        foreach ($values as $value) {
            $this->bindings['where'][] = $value;
        }

        return $this;
    }

    public function whereNotIn($column, array $values, $boolean = 'and')
    {
        $this->wheres[] = [
            'type' => 'not in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean
        ];

        foreach ($values as $value) {
            $this->bindings['where'][] = $value;
        }

        return $this;
    }

    /**
     * Add a nested where statement to the query.
     *
     * @param  \Closure  $callback
     * @param  string    $boolean
     * @return $this
     */
    protected function whereNested(\Closure $callback, $boolean = 'and')
    {
        $query = new self();
        $query->setModel($this->model)->setConnection($this->connectionName);

        $callback($query);

        if (count($query->wheres)) {
            $this->wheres[] = [
                'type' => 'nested',
                'query' => $query,
                'boolean' => $boolean
            ];

            $this->bindings['where'] = array_merge(
                $this->bindings['where'],
                $query->bindings['where']
            );
        }

        return $this;
    }


    /**
     * Compile the select statement.
     *
     * @return string
     */
    protected function compileSelect()
    {
        $components = [
            'select' => $this->compileColumns(),
            'from' => $this->compileFrom(),
            'where' => $this->compileWheres(),
            'order' => $this->compileOrders(),
            'limit' => $this->compileLimit(),
            'offset' => $this->compileOffset(),
        ];

        $sql = trim(implode(' ', array_filter($components)));
        return $sql;
    }
}
