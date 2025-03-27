<?php

namespace Axcel\AxcelCore\Eloquent\Database\Builder\Traits;

trait Compiler
{
    /**
     * Compile the columns for the select clause.
     *
     * @return string
     */
    protected function compileColumns()
    {
        $columns = $this->columns;

        if (empty($columns)) {
            $columns = ['*'];
        }

        return 'SELECT ' . implode(', ', $columns);
    }


    /**
     * Compile the from clause.
     *
     * @return string
     */
    protected function compileFrom()
    {
        return 'FROM ' . $this->table;
    }

    /**
     * Compile the WHERE conditions into SQL.
     *
     * @return string
     */
    protected function compileWheres()
    {
        if (empty($this->wheres)) {
            return '';
        }

        $sql = [];
        $bindings = [];

        foreach ($this->wheres as $where) {
            switch ($where['type']) {
                case 'basic':
                    $sql[] = sprintf(
                        '%s %s ?',
                        $where['column'],
                        $where['operator']
                    );
                    $bindings[] = $where['value'];
                    break;

                case 'in':
                    $placeholders = implode(',', array_fill(0, count($where['values']), '?'));
                    $sql[] = sprintf(
                        '%s %s (%s)',
                        $where['column'],
                        $where['type'] === 'in' ? 'IN' : 'NOT IN',
                        $placeholders
                    );
                    $bindings = array_merge($bindings, $where['values']);
                    break;

                case 'nested':
                    // Handle nested where clauses
                    $nestedQuery = $where['query'];
                    $nestedWheres = $nestedQuery->compileWheres();
                    if (!empty($nestedWheres)) {
                        $sql[] = '(' . $nestedWheres . ')';
                        $bindings = array_merge($bindings, $nestedQuery->getBindings()['where']);
                    }
                    break;
            }
        }

        // Reset bindings to ensure only current query's bindings are used
        $this->bindings['where'] = $bindings;

        return $sql ? 'WHERE ' . implode(' AND ', $sql) : '';
    }

    /**
     * Compile the order by clauses.
     *
     * @return string|null
     */
    protected function compileOrders()
    {
        if (empty($this->orders)) {
            return null;
        }

        $orderStatements = [];

        foreach ($this->orders as $order) {
            $orderStatements[] = "{$order['column']} {$order['direction']}";
        }

        return 'ORDER BY ' . implode(', ', $orderStatements);
    }

    /**
     * Compile the "limit" portion of the query.
     *
     * @return string|null
     */
    protected function compileLimit()
    {
        if (isset($this->limit)) {
            return 'LIMIT ' . (int) $this->limit;
        }

        return null;
    }

    /**
     * Compile the "offset" portion of the query.
     *
     * @return string|null
     */
    protected function compileOffset()
    {
        if (isset($this->offset)) {
            return 'OFFSET ' . (int) $this->offset;
        }

        return null;
    }


    /**
     * Prepare a SQL statement for execution.
     *
     * @return \PDOStatement
     */
    public function prepareStatement()
    {
        $sql = $this->compileSelect();
        $statement = $this->getConnection()->prepare($sql);

        // Bind all values in their correct order
        $index = 1;
        foreach ($this->bindings as $type => $bindings) {
            foreach ($bindings as $binding) {
                $statement->bindValue($index++, $binding);
            }
        }

        return $statement;
    }
}
