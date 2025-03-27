<?php

namespace Axcel\AxcelCore\Eloquent\Foundations\Traits;

use Axcel\Core\Eloquent\Foundations\Collection;
use PDO;

trait EagerLoading
{
    /* The relationships that should be eager loaded.
    *
    * @var array
    */
    protected static $eagerLoad = [];

    /**
     * Eager load relationships.
     *
     * @param array|string $relations
     * @return $this
     */
    public static function with($relations)
    {
        $relations = is_string($relations) ? func_get_args() : $relations;

        static::$eagerLoad = array_merge(static::$eagerLoad, $relations);

        return new static;
    }

    /**
     * Get the eager loading results.
     *
     * @param Collection $results
     * @return Collection
     */
    protected function eagerLoadRelations($results)
    {
        foreach (self::$eagerLoad as $name) {
            $this->eagerLoadRelation($results, $name);
        }

        return $results;
    }

    /**
     * Eager load a single relationship.
     *
     * @param Collection $results
     * @param string $name
     * @return void
     */
    protected function eagerLoadRelation($results, $name)
    {
        // If results are empty, do nothing
        if (empty($results) || count($results) === 0) {
            return;
        }

        // Get the first model to determine the relation method
        $first = $results->first();

        // Check if the relation method exists
        if (!method_exists($first, $name)) {
            throw new \Exception("Relationship method [{$name}] does not exist.");
        }

        // Call the relation method to get the Relation instance
        $relation = $first->$name();

        // Match the results to the parent models
        $relation->match($results);
    }
}
