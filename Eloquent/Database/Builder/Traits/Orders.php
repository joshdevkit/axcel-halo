<?php

namespace App\Core\Eloquent\Database\Builder\Traits;

use Exception;

trait Orders
{
    /**
     * Add an order by clause to the query.
     *
     * @param string $column
     * @param string $direction
     * @return $this
     */
    public function orderBy($column, $direction = 'asc')
    {
        $direction = strtolower($direction);

        if (!in_array($direction, ['asc', 'desc'])) {
            throw new Exception('Order direction must be "asc" or "desc".');
        }

        $this->orders[] = compact('column', 'direction');
        return $this;
    }

    /**
     * Set the limit for the query.
     *
     * @param int $value
     * @return $this
     */
    public function limit($value)
    {
        $this->limit = $value;
        return $this;
    }

    /**
     * Set the offset for the query.
     *
     * @param int $value
     * @return $this
     */
    public function offset($value)
    {
        $this->offset = $value;
        return $this;
    }

    /**
     * Alias for the "offset" method.
     *
     * @param int $value
     * @return $this
     */
    public function skip($value)
    {
        return $this->offset($value);
    }

    /**
     * Set the "limit" and "offset" for the query.
     *
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function take($limit, $offset = 0)
    {
        $this->limit($limit);
        if ($offset) {
            $this->offset($offset);
        }
        return $this;
    }
}
