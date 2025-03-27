<?php

namespace Axcel\AxcelCore\Eloquent\Database\Builder\Traits;

use Axcel\Core\Eloquent\Foundations\Events\ModelEvent;
use Axcel\Core\Eloquent\Foundations\Model;

trait ModelEvents
{
    /**
     * The registered model event callbacks.
     *
     * @var array
     */
    protected static $eventCallbacks = [];

    /**
     * Register a model event callback.
     *
     * @param string $event
     * @param \Closure $callback
     * @return void
     */
    public static function registerEvent(string $event, \Closure $callback)
    {
        static::$eventCallbacks[$event][] = $callback;
    }

    /**
     * Fire a model event.
     *
     * @param string $event
     * @param Model $model
     * @return bool
     */
    protected function fireModelEvent(string $event, Model $model)
    {
        $callbacks = static::$eventCallbacks[$event] ?? [];

        foreach ($callbacks as $callback) {
            $result = $callback($model);

            // If any callback returns false, stop further processing
            if ($result === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Register global model events
     */
    protected static function registerModelEvents()
    {
        // Default event registration methods
        $eventMethods = [
            'creating',
            'created',
            'updating',
            'updated',
            'saving',
            'saved',
            'deleting',
            'deleted',
            'restoring',
            'restored'
        ];

        foreach ($eventMethods as $method) {
            if (method_exists(static::class, $method)) {
                static::registerEvent($method, function ($model) use ($method) {
                    return static::$method($model);
                });
            }
        }
    }

    /**
     * Perform the save operation with event handling
     * 
     * @return bool
     */
    public function save()
    {
        // Fire saving event
        if ($this->fireModelEvent(ModelEvent::SAVING, $this) === false) {
            return false;
        }

        // Determine if this is a create or update operation
        $isNewRecord = !$this->exists;

        // Fire creating/updating event
        $beforeEvent = $isNewRecord ? ModelEvent::CREATING : ModelEvent::UPDATING;
        if ($this->fireModelEvent($beforeEvent, $this) === false) {
            return false;
        }

        // Perform the actual save operation
        $saved = $this->builder()->saveModel($this);

        if ($saved) {
            // Fire created/updated event
            $afterEvent = $isNewRecord ? ModelEvent::CREATED : ModelEvent::UPDATED;
            $this->fireModelEvent($afterEvent, $this);

            // Fire saved event
            $this->fireModelEvent(ModelEvent::SAVED, $this);
        }

        return $saved;
    }

    /**
     * Perform the delete operation with event handling
     * 
     * @return bool
     */
    public function delete()
    {
        // Fire deleting event
        if ($this->fireModelEvent(ModelEvent::DELETING, $this) === false) {
            return false;
        }

        // Perform the actual delete operation
        $deleted = $this->builder()->delete();

        if ($deleted) {
            // Fire deleted event
            $this->fireModelEvent(ModelEvent::DELETED, $this);
        }

        return $deleted;
    }

    /**
     * Example event method - can be overridden in specific models
     */
    protected function saving(Model $model)
    {
        // Validation or other pre-save logic
        return true;
    }

    /**
     * Example of how to register global event listeners
     * 
     * @return void
     */
    public static function boot()
    {
        static::registerModelEvents();

        // Example of registering a global event listener
        static::registerEvent(ModelEvent::CREATING, function ($model) {
            // Global creating logic
            // For example, setting a default value
            if (empty($model->created_at)) {
                $model->created_at = date('Y-m-d H:i:s');
            }
            return true;
        });
    }
}
