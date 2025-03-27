<?php

namespace App\Core\Eloquent\Database\Builder\Traits;

trait Counting
{

    /**
     * Get the count of the total records.
     *
     * @return int
     */
    public function count()
    {
        $clone = clone $this;
        $clone->columns = ['COUNT(*) as aggregate'];

        $result = $clone->first();

        return (int) $result->aggregate;
    }

    /**
     * Get the maximum value of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function max($column)
    {
        $clone = clone $this;
        $clone->columns = ["MAX({$column}) as aggregate"];

        $result = $clone->first();

        return $result->aggregate;
    }

    /**
     * Get the minimum value of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function min($column)
    {
        $clone = clone $this;
        $clone->columns = ["MIN({$column}) as aggregate"];

        $result = $clone->first();

        return $result->aggregate;
    }

    /**
     * Get the sum of the values of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function sum($column)
    {
        $clone = clone $this;
        $clone->columns = ["SUM({$column}) as aggregate"];

        $result = $clone->first();

        return $result->aggregate;
    }

    /**
     * Get the average of the values of a given column.
     *
     * @param string $column
     * @return mixed
     */
    public function avg($column)
    {
        $clone = clone $this;
        $clone->columns = ["AVG({$column}) as aggregate"];

        $result = $clone->first();

        return $result->aggregate;
    }
}
