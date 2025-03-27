<?php

namespace Axcel\AxcelCore\Eloquent\Foundations\Traits;

trait JoinClause
{
    /**
     * The joins for the query.
     *
     * @var array
     */
    protected $joins = [];

    /**
     * Add a join clause to the query.
     *
     * @param string $table
     * @param string $first
     * @param string|null $operator
     * @param string|null $second
     * @param string $type
     * @return $this
     */
    public function join($table, $first, $operator = null, $second = null, $type = 'INNER')
    {
        // If only two parameters are passed, assume column1 = column2
        if (func_num_args() === 2) {
            $second = $operator;
            $operator = '=';
        }

        $join = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => strtoupper($type)
        ];

        $this->joins[] = $join;
        $this->bindings['join'][] = $second;

        return $this;
    }

    /**
     * Add a left join to the query.
     *
     * @param string $table
     * @param string $first
     * @param string|null $operator
     * @param string|null $second
     * @return $this
     */
    public function leftJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'LEFT');
    }

    /**
     * Add a right join to the query.
     *
     * @param string $table
     * @param string $first
     * @param string|null $operator
     * @param string|null $second
     * @return $this
     */
    public function rightJoin($table, $first, $operator = null, $second = null)
    {
        return $this->join($table, $first, $operator, $second, 'RIGHT');
    }

    /**
     * Compile the joins for the query.
     *
     * @return string
     */
    protected function compileJoins()
    {
        $sql = '';

        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} ?";
        }

        return $sql;
    }

    // /**
    //  * Modify the select method to include joins in the SQL compilation
    //  */
    // protected function compileSelect()
    // {
    //     $columns = $this->columns ? implode(', ', $this->columns) : '*';

    //     $sql = "SELECT {$columns} FROM {$this->table}";

    //     // Add joins
    //     if (!empty($this->joins)) {
    //         $sql .= $this->compileJoins();
    //     }

    //     // Add where conditions
    //     if (!empty($this->wheres)) {
    //         $sql .= " " . $this->compileWheres();
    //     }

    //     // Add ordering
    //     if (!empty($this->orders)) {
    //         $sql .= " " . $this->compileOrders();
    //     }

    //     // Add limit
    //     if ($this->limit !== null) {
    //         $sql .= " LIMIT {$this->limit}";
    //     }

    //     // Add offset
    //     if ($this->offset !== null) {
    //         $sql .= " OFFSET {$this->offset}";
    //     }

    //     return $sql;
    // }

    // /**
    //  * Modify the prepareStatement method to include join bindings
    //  */
    // protected function prepareStatement()
    // {
    //     $sql = $this->compileSelect();
    //     $statement = $this->getConnection()->prepare($sql);

    //     $bindingValues = array_merge(
    //         $this->bindings['select'],
    //         $this->bindings['join'],
    //         $this->bindings['where'],
    //         $this->bindings['order']
    //     );

    //     return $statement;
    // }
}
