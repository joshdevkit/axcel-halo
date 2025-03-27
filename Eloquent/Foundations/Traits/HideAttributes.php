<?php

namespace App\Core\Eloquent\Foundations\Traits;

trait HideAttributes
{
    /**
     * The attributes that are hidden from array or JSON output.
     */
    protected $hidden = [];

    /**
     * Get the value of an attribute, applying the hidden attribute logic.
     */
    public function getAttribute($key)
    {
        // Allow direct access to hidden attributes internally (for authentication)
        if (in_array($key, $this->hidden ?? []) && $this->isBeingSerialized()) {
            return null;
        }

        $value = $this->attributes[$key] ?? null;

        return $this->applyCasts($key, $value);
    }

    /**
     * Check if the model is being converted to an array or JSON
     */
    protected function isBeingSerialized()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        return isset($trace[1]['function']) && in_array($trace[1]['function'], ['toArray', 'jsonSerialize']);
    }


    /**
     * Set the value of an attribute.
     */
    public function setAttribute($key, $value)
    {
        $this->attributes[$key] = $value;

        return $this;
    }

    /**
     * Get the hidden attributes on the model.
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * Determine if an attribute should be hidden.
     */
    public function hideAttribute($key)
    {
        if (!in_array($key, $this->hidden)) {
            $this->hidden[] = $key;
        }
    }

    /**
     * Determine if an attribute exists in the model's attributes array.
     */
    public function __isset($key)
    {
        return isset($this->attributes[$key]) && !in_array($key, $this->hidden);
    }
}
