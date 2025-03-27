<?php

namespace App\Core\Eloquent\Foundations;

use App\Core\Attributes\Str;
use App\Core\Eloquent\Database\Builder\Builder;
use App\Core\Eloquent\Database\ConnectionManager;
use App\Core\Eloquent\Foundations\Traits\GuardsAttributes;
use App\Core\Eloquent\Foundations\Traits\CastAttribute;
use App\Core\Eloquent\Foundations\Traits\HasBuilderMethods;
use App\Core\Eloquent\Foundations\Traits\HideAttributes;
use App\Core\Eloquent\Relations\BelongsTo;
use App\Core\Eloquent\Relations\BelongsToMany;
use App\Core\Eloquent\Relations\HasMany;
use App\Core\Eloquent\Relations\HasOne;
use App\Core\Exceptions\StaticMethodException;
use Exception;
use PDO;
use ReflectionMethod;

abstract class Model
{
    use CastAttribute, GuardsAttributes, HideAttributes, HasBuilderMethods;


    /**
     * get the child table of the model
     *
     * @var static table
     */
    protected  $table;



    /**
     * Connection string
     *
     * @var string default connection
     */

    protected static $connection = 'default';


    /**
     * Model Attributes
     *
     * @var array attributes
     */
    protected $attributes = [];

    /**
     * Model Original columns
     *
     * @var array model columns
     */
    protected $original = [];


    /**
     * Mass Assignment 
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Default guarded
     *
     * @var array 
     */
    protected $guarded = ['*'];
    /**
     * Primary key
     *
     * @var string
     */
    protected $primaryKey = 'id';


    /**
     * Model record exist
     *
     * @var boolean
     */
    protected $exists = false;


    /**
     * Relations model
     * @var array
     */

    protected $relations = [];

    /**
     * The callback that is responsible for handling discarded attribute violations.
     *
     * @var callable|null
     */
    protected static $discardedAttributeViolationCallback;

    /**
     * Indicates whether models should prevent silently discarding attributes.
     *
     * @var bool
     */
    protected static $modelsShouldPreventSilentlyDiscardingAttributes = true;


    public static function preventsSilentlyDiscardingAttributes()
    {
        return static::$modelsShouldPreventSilentlyDiscardingAttributes;
    }

    public function __construct(array $attributes = [])
    {
        if (!isset($this->table) || empty($this->table)) {
            $this->setTable(null);
        }
        $this->fill($attributes);
    }

    public function __call($method, $arguments)
    {
        if (method_exists($this, $method)) {
            return $this->$method(...$arguments);
        }

        return $this->handleDynamicMethod($method, $arguments);
    }

    public static function __callStatic($method, $parameters)
    {

        $instance = new static;
        $query = $instance->builder();

        if (method_exists($query, $method)) {
            return $query->$method(...$parameters);
        }

        throw new StaticMethodException(
            static::class,
            $method,
            $parameters
        );
    }

    protected function handleDynamicMethod($method, $arguments)
    {
        try {
            $reflection = new ReflectionMethod($this, $method);

            if ($reflection->isPublic()) {
                return $reflection->invokeArgs($this, $arguments);
            }
        } catch (\ReflectionException $e) {
            $query = $this->builder();
            if (method_exists($query, $method)) {
                return $query->$method(...$arguments);
            }
            if (method_exists($this, $method)) {
                return $this->$method(...$arguments);
            }
            throw new Exception("Method {$method} doesn't exist.");
        }

        throw new \BadMethodCallException("Method {$method} not defined.");
    }

    /**
     * Get the database connection
     * 
     * @return PDO
     */
    protected function getConnection()
    {
        return ConnectionManager::getPdo(static::$connection);
    }

    public function builder()
    {
        $builder = new Builder();
        $builder->setModel($this)->setConnection(static::$connection);
        return $builder;
    }

    public static function query()
    {
        return (new static)->builder();
    }

    /**
     * Set the database connection name
     * 
     * @param string $name
     * @return $this
     */
    public function setConnection(string $name)
    {
        static::$connection = $name;
        return $this;
    }


    public function fill(array $attributes)
    {
        $totallyGuarded = $this->totallyGuarded();
        $fillable = $this->fillableFromArray($attributes);
        foreach ($fillable as $key => $value) {
            if ($this->isFillable($key)) {
                $this->setAttribute($key, $value);
            } elseif ($totallyGuarded || static::preventsSilentlyDiscardingAttributes()) {
                if (isset(static::$discardedAttributeViolationCallback)) {
                    call_user_func(static::$discardedAttributeViolationCallback, $this, [$key]);
                } else {
                    throw new Exception(sprintf(
                        'Add [%s] to fillable property to allow mass assignment on [%s].',
                        $key,
                        get_class($this)
                    ));
                }
            }
        }

        if (
            count($attributes) !== count($fillable) &&
            static::preventsSilentlyDiscardingAttributes()
        ) {
            $keys = array_diff(array_keys($attributes), array_keys($fillable));

            if (isset(static::$discardedAttributeViolationCallback)) {
                call_user_func(static::$discardedAttributeViolationCallback, $this, $keys);
            } else {
                throw new Exception(sprintf(
                    'Add fillable property [%s] to allow mass assignment on [%s].',
                    implode(', ', $keys),
                    get_class($this)
                ));
            }
        }

        // dd($this);
        return $this;
    }

    /**
     * Set the model's raw attributes
     */
    public function setRawAttributes(array $attributes)
    {
        foreach ($attributes as $key => $value) {
            if ($key === 'password') {
                $this->attributes[$key] = $value;
            } else {
                $this->setAttribute($key, $value);
            }
        }
        $this->original = $attributes;
        $this->exists = true;
        // dd($this);
        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getTable()
    {
        return $this->table ?: strtolower(Str::plural(Str::classBasename(static::class)));
    }


    /**
     * Set the table associated with the model.
     *
     * @param  string|null  $table
     * @return $this
     */
    public function setTable($table = null)
    {
        $this->table = $table ?: strtolower(Str::plural(Str::classBasename(static::class)));
        return $this;
    }


    /**
     * Get the primary key for the model
     */
    public function getPrimaryKey()
    {
        return $this->primaryKey;
    }

    /**
     * Set the primary key for the model.
     *
     * @param  string  $key
     * @return $this
     */
    public function setPrimaryKey($key)
    {
        $this->primaryKey = $key;

        return $this;
    }



    /**
     * Magic setter for attributes
     */
    public function __set($key, $value)
    {
        $this->setAttribute($key, $value);
    }

    public function getKey()
    {
        return $this->attributes[$this->getPrimaryKey()] ?? null;
    }


    public function __get($key)
    {
        // If relation is already loaded, return it
        if (isset($this->relations[$key])) {
            return $this->relations[$key];
        }

        // If method exists for relation, fetch and store it in $relations
        if (method_exists($this, $key)) {
            $relationQuery = $this->$key();

            if ($relationQuery instanceof Builder) {
                // For hasOne, use first() to get a single record
                // For hasMany, use get() to get a collection
                $this->relations[$key] = method_exists($this, $key . 'Relation')
                    ? $relationQuery->first()
                    : $relationQuery->get();

                return $this->relations[$key];
            }
        }

        // If it's a normal attribute, return it
        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        throw new Exception("Property {$key} not found.");
    }


    /**
     * Eager load relations.
     *
     * @param array|string $relations
     * @return $this
     */
    public function load($relations)
    {
        $relations = is_array($relations) ? $relations : func_get_args();

        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $relationQuery = $this->$relation();

                if ($relationQuery instanceof Builder) {
                    $this->relations[$relation] = $relationQuery->first(); // ✅ Store in relations array
                }
            }
        }

        return $this;
    }


    /**
     * Set a relationship on the model.
     *
     * @param  string  $relation
     * @param  mixed  $value
     * @return void
     */
    public function setRelation($relation, $value)
    {
        $this->relations[$relation] = $value;
    }



    public function hasOne($related, $foreignKey = null, $localKey = null)
    {
        $relatedModel = new $related;

        // Use primary key if no local key is provided
        $localKey = $localKey ?: $this->getPrimaryKey();

        // Use `{model}_id` convention for the foreign key if not provided
        $foreignKey = $foreignKey ?: strtolower(class_basename(get_class($this))) . '_id';

        // Create a new query and apply the where condition
        return $relatedModel->builder()
            ->where($foreignKey, '=', $this->getKey())
            ->limit(1);
    }
    public function hasMany($related, $foreignKey = null, $localKey = null)
    {
        $relatedModel = new $related;

        // Use primary key if no local key is provided
        $localKey = $localKey ?: $this->getPrimaryKey();

        // Use `{model}_id` convention for the foreign key if not provided
        $foreignKey = $foreignKey ?: strtolower(class_basename(get_class($this))) . '_id';

        // Modify the HasMany class to filter the results
        return (new HasMany($this, $relatedModel, $foreignKey, $localKey))
            ->getRelationQuery()
            ->where($foreignKey, '=', $this->getKey());
    }

    public function belongsTo($related, $foreignKey = null, $ownerKey = null)
    {
        $relatedModel = new $related;

        $foreignKey = $foreignKey ?: strtolower(class_basename(get_class($relatedModel))) . '_id';
        $ownerKey = $ownerKey ?: $relatedModel->getPrimaryKey();

        return (new BelongsTo($this, $relatedModel, $foreignKey, $ownerKey))->getRelationQuery();
    }

    public function belongsToMany($related, $pivotTable, $foreignKey = null, $relatedKey = null, $parentKey = null, $relatedParentKey = null)
    {
        $relatedModel = new $related;

        $parentKey = $parentKey ?: $this->getPrimaryKey();
        $relatedParentKey = $relatedParentKey ?: $relatedModel->getPrimaryKey();

        $foreignKey = $foreignKey ?: strtolower(class_basename(get_class($this))) . '_id';
        $relatedKey = $relatedKey ?: strtolower(class_basename(get_class($relatedModel))) . '_id';

        return (new BelongsToMany($this, $relatedModel, $pivotTable, $foreignKey, $relatedKey, $parentKey, $relatedParentKey))->getRelationQuery();
    }
}
