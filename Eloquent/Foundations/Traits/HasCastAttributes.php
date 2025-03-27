<?php

namespace App\Core\Eloquent\Foundations\Traits;

trait HasCastAttributes
{
    /**
     * Cast the model's attributes based on the defined casts.
     *
     * @param  array  $attributes
     * @param  array  $casts
     * @return array
     */
    protected function castAttributes(array $attributes, array $casts)
    {
        foreach ($casts as $key => $type) {
            if (array_key_exists($key, $attributes)) {
                $attributes[$key] = $this->castAttribute($key, $attributes[$key], $type);
            }
        }

        return $attributes;
    }

    /**
     * Cast a single attribute to the desired type.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  string  $type
     * @return mixed
     */
    protected function castAttribute($key, $value, $type)
    {
        switch ($type) {
            case 'hashed':
                return password_hash($value, PASSWORD_BCRYPT);
            case 'datetime':
                return $this->asDateTime($value);
            default:
                return $value;
        }
    }

    protected function asDateTime($value)
    {
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i:s');
        }

        return $value ? (new \DateTime($value))->format('Y-m-d H:i:s') : null;
    }
}
