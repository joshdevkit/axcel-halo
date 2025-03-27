<?php


namespace Axcel\AxcelCore\Eloquent\Relations;

use Axcel\Core\Eloquent\Database\Builder\Builder;
use Axcel\Core\Eloquent\Foundations\Model;

class HasOne extends Relation
{
    public function __construct(Model $parent, Model $related, $foreignKey, $localKey)
    {
        parent::__construct($parent, $related);
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    /**
     * Get the result of the relationship.
     */
    public function getResults()
    {
        return $this->related->query()->where($this->foreignKey, $this->parent->{$this->localKey})->first();
    }

    /**
     * Match the related model with the parent models (used in eager loading).
     */
    public function match($models)
    {
        $foreignKey = $this->foreignKey;
        $localKey = $this->localKey;

        // Fetch related models in one query (Eager Loading)
        $relatedModels = $this->related->query()
            ->whereIn($foreignKey, array_column($models, $localKey))
            ->get()
            ->keyBy($foreignKey);

        // Attach the related models to their corresponding parent
        foreach ($models as $model) {
            $model->setRelation($this->getRelationName(), $relatedModels[$model->$localKey] ?? null);
        }

        return $models;
    }

    /**
     * Return the relationship query.
     */
    public function getRelationQuery(): Builder
    {
        return $this->related->query()->where($this->foreignKey, $this->parent->{$this->localKey});
    }

    /**
     * Get the name of the relation.
     */
    protected function getRelationName()
    {
        return strtolower(class_basename(get_class($this->related)));
    }
}
