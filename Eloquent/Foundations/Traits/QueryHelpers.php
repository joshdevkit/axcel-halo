<?php

namespace Axcel\AxcelCore\Eloquent\Foundations\Traits;

trait QueryHelpers
{
    /**
     * Add a "WHERE IN" clause to the query.
     */
    public function whereIn($column, array $values)
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));
        // Store this as an array, not a string, to match the expected format
        $this->wheres[] = ['column' => $column, 'operator' => 'IN', 'values' => $values];
        $this->bindings['where'] = array_merge($this->bindings['where'], $values);

        return $this;
    }

    /**
     * Add a "WHERE NOT IN" clause to the query.
     */
    public function whereNotIn($column, array $values)
    {
        if (empty($values)) {
            return $this;
        }

        $placeholders = implode(',', array_fill(0, count($values), '?'));
        // Store this as an array, not a string, to match the expected format
        $this->wheres[] = [
            'column' => $column,
            'operator' => 'NOT IN',
            'values' => $values,
        ];
        $this->bindings['where'] = array_merge($this->bindings['where'], $values);

        return $this;
    }

    /**
     * Add a raw "WHERE" condition to the query.
     */
    public function whereRaw($condition, array $bindings = [])
    {
        $this->wheres[] = $condition;
        $this->bindings['where'] = array_merge($this->bindings['where'], $bindings);

        return $this;
    }

    /**
     * Add a "WHERE NULL" condition.
     */
    public function whereNull($column)
    {
        return $this->whereRaw("$column IS NULL");
    }

    /**
     * Add a "WHERE NOT NULL" condition.
     */
    public function whereNotNull($column)
    {
        return $this->whereRaw("$column IS NOT NULL");
    }

    /**
     * Add an "OR WHERE" condition to the query.
     */
    public function orWhere($column, $operator, $value)
    {
        $this->wheres[] = ['column' => $column, 'operator' => $operator, 'value' => $value, 'boolean' => 'OR'];
        $this->bindings['where'] = array_merge($this->bindings['where'], [$value]);

        return $this;
    }

    /**
     * Add a "WHERE BETWEEN" condition to the query.
     */
    public function whereBetween($column, array $values)
    {
        if (count($values) !== 2) {
            throw new \InvalidArgumentException('The "whereBetween" method requires an array with exactly two values.');
        }

        $this->wheres[] = ['column' => $column, 'operator' => 'BETWEEN', 'values' => $values];
        $this->bindings['where'] = array_merge($this->bindings['where'], $values);

        return $this;
    }

    /**
     * Add a "WHERE LIKE" condition to the query.
     */
    public function whereLike($column, $value)
    {
        $this->wheres[] = ['column' => $column, 'operator' => 'LIKE', 'value' => '%' . $value . '%'];
        $this->bindings['where'] = array_merge($this->bindings['where'], [$value]);

        return $this;
    }

    /**
     * Add a "WHERE DATE" condition to the query.
     */
    public function whereDate($column, $date)
    {
        $this->wheres[] = ['column' => $column, 'operator' => '=', 'value' => $date];
        $this->bindings['where'] = array_merge($this->bindings['where'], [$date]);

        return $this;
    }
}
