<?php

namespace Axcel\AxcelCore\Eloquent\Relations;

use Axcel\Core\Eloquent\Foundations\Model;
use Axcel\Core\Eloquent\Foundations\Collection;

abstract class Relation
{
    protected $parent;
    protected $related;
    protected $foreignKey;
    protected $localKey;
    protected $eagerLoad = [];

    public function __construct(Model $parent, Model $related)
    {
        $this->parent = $parent;
        $this->related = $related;
    }

    abstract public function getResults();

    abstract public function match(Collection $models);
}
