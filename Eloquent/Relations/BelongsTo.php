<?php

namespace App\Core\Eloquent\Relations;

use App\Core\Eloquent\Foundations\Model;
use App\Core\Eloquent\Database\Builder\Builder;

class BelongsTo extends Relation
{
    protected $foreignKey;
    protected $ownerKey;

    public function __construct(Model $parent, Model $related, $foreignKey, $ownerKey)
    {
        parent::__construct($parent, $related);
        $this->foreignKey = $foreignKey;
        $this->ownerKey = $ownerKey;
    }

    /**
     * Get the query for the related model.
     *
     * @return Builder
     */
    public function getRelationQuery()
    {
        return $this->related->query()->where($this->ownerKey, $this->parent->{$this->foreignKey});
    }

    public function getForeignKey()
    {
        return $this->foreignKey;
    }

    public function getOwnerKey()
    {
        return $this->ownerKey;
    }
    /**
     * Get the related model result.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->getRelationQuery()->first();
    }

    /**
     * Match the related model to its parent.
     *
     * @param  \App\Core\Eloquent\Foundations\Collection  $models
     * @return void
     */
    public function match($models)
    {
        foreach ($models as $model) {
            $relatedModel = $this->related->query()->where($this->ownerKey, $model->{$this->foreignKey})->first();
            $model->setRelation($this->getRelationName(), $relatedModel);
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
