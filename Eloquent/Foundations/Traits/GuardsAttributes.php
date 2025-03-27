<?php

namespace App\Core\Eloquent\Foundations\Traits;

use Exception;

trait GuardsAttributes
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [];

    /**
     * The attributes that are not mass assignable.
     *
     * @var array
     */
    protected $guarded = ["*"];

    /**
     * Indicates if all mass assignment is enabled.
     *
     * @var bool
     */
    protected static $unguarded = false;

    /**
     * Get the fillable attributes for the model.
     *
     * @return array<string>
     */
    public function getFillable()
    {
        return property_exists($this, 'fillable') ? $this->fillable : [];
    }

    /**
     * Set the fillable attributes for the model.
     *
     * @param  array<string>  $fillable
     * @return $this
     */
    public function fillable(array $fillable)
    {
        $this->fillable = $fillable;
        return $this;
    }

    /**
     * Guard against mass-assignment of non-fillable attributes.
     *
     * @param  array  $attributes
     * @return void
     * @throws \Exception
     */
    public function guardAttributes(array $attributes)
    {
        // ✅ Verify if attributes are fillable
        foreach ($attributes as $key => $value) {
            if (!$this->isFillable($key)) {
                throw new Exception("Mass assignment failed: The attribute [$key] is not fillable in " . get_class($this));
            }
        }
    }

    /**
     * Check if all attributes are guarded.
     *
     * @return bool
     */
    public function totallyGuarded()
    {
        return empty($this->getFillable()) && empty($this->guarded);
    }

    /**
     * Return the fillable attributes from an array of attributes.
     *
     * @param  array  $attributes
     * @return array
     */
    public function fillableFromArray(array $attributes)
    {
        $fillable = $this->getFillable(); // Get the fillable attributes

        if (empty($fillable)) {
            throw new Exception("Mass assignment failed: No fillable attributes are defined in " . get_class($this));
        }

        return array_intersect_key($attributes, array_flip($fillable));
    }

    /**
     * Check if the attribute is fillable.
     *
     * @param  string  $key
     * @return bool
     */
    public function isFillable($key)
    {
        return in_array($key, $this->getFillable());
    }
}
