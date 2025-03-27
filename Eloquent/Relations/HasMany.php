<?php

namespace App\Core\Eloquent\Relations;

use Axcel\Core\Eloquent\Foundations\Model;
use Axcel\Core\Eloquent\Database\Builder\Builder;

class HasMany extends Relation
{
    protected $foreignKey;
    protected $localKey;

    public function __construct(Model $parent, Model $related, $foreignKey, $localKey)
    {
        parent::__construct($parent, $related);
        $this->foreignKey = $foreignKey;
        $this->localKey = $localKey;
    }

    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    public function getLocalKey()
    {
        return $this->localKey;
    }
    /**
     * Get the query for the related model.
     *
     * @return Builder
     */
    public function getRelationQuery()
    {
        return $this->related->query()->where($this->foreignKey, $this->parent->getKey());
    }

    /**
     * Get the related model results.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->getRelationQuery()->get();
    }

    /**
     * Match the related models to their parents.
     *
     * @param  \App\Core\Eloquent\Foundations\Collection  $models
     * @return void
     */
    public function match($models)
    {
        foreach ($models as $model) {
            $relatedModels = $this->related->query()->where($this->foreignKey, $model->getKey())->get();
            $model->setRelation($this->getRelationName(), $relatedModels);
        }
    }

    /**
     * Get the name of the relation.
     *
     * @return string
     */
    protected function getRelationName()
    {
        return strtolower(class_basename($this->related));
    }
}
