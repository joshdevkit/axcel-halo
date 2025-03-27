<?php

namespace App\Core\Eloquent\Relations;

use App\Core\Eloquent\Foundations\Model;
use App\Core\Eloquent\Database\Builder\Builder;

class BelongsToMany extends Relation
{
    protected $pivotTable;
    protected $foreignKey;
    protected $relatedKey;
    protected $parentKey;
    protected $relatedParentKey;

    public function __construct(Model $parent, Model $related, $pivotTable, $foreignKey, $relatedKey, $parentKey, $relatedParentKey)
    {
        parent::__construct($parent, $related);
        $this->pivotTable = $pivotTable;
        $this->foreignKey = $foreignKey;
        $this->relatedKey = $relatedKey;
        $this->parentKey = $parentKey;
        $this->relatedParentKey = $relatedParentKey;
    }

    /**
     * Get the query for the related model.
     *
     * @return Builder
     */
    public function getRelationQuery()
    {
        return $this->related->query()
            ->join($this->pivotTable, "{$this->related->getTable()}.{$this->relatedParentKey}", '=', "{$this->pivotTable}.{$this->relatedKey}")
            ->where("{$this->pivotTable}.{$this->foreignKey}", $this->parent->{$this->parentKey});
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
     * Match the related models to their parent.
     *
     * @param  \App\Core\Eloquent\Foundations\Collection  $models
     * @return void
     */
    public function match($models)
    {
        $keys = $models->pluck($this->parentKey)->toArray();
        $relatedModels = $this->getRelationQuery()->whereIn("{$this->pivotTable}.{$this->foreignKey}", $keys)->get();

        foreach ($models as $model) {
            $model->setRelation($this->getRelationName(), $relatedModels->filter(function ($relatedModel) use ($model) {
                return $relatedModel->{$this->foreignKey} === $model->{$this->parentKey};
            }));
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
