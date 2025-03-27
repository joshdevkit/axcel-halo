<?php

namespace Axcel\AxcelCore\Eloquent\Foundations\Traits;

use Axcel\AxcelCore\Application;
use DateTime;

trait CastAttribute
{
    /**
     * Apply type casting to an attribute.
     */
    protected function applyCasts($key, $value)
    {
        if (!isset($this->casts[$key])) {
            return $value;
        }

        $castType = $this->casts[$key];

        switch ($castType) {
            case 'int':
            case 'integer':
                return (int) $value;
            case 'float':
            case 'double':
                return (float) $value;
            case 'bool':
            case 'boolean':
                return (bool) $value;
            case 'string':
                return (string) $value;
            case 'datetime':
                return (new DateTime($value))->format('Y-m-d H:i:s');
            case 'hashed':
                $hasher = Application::getInstance()->make('hash');
                if ($hasher->needsRehash($value)) {
                    return $hasher->make($value);
                }
                return $value;

            case 'array':
                return json_decode($value, true);
            case 'object':
                return json_decode($value);
            default:
                return $value;
        }
    }


    /**
     * Convert attributes to array with casting.
     */
    public function toArray()
    {
        $array = [];
        foreach ($this->attributes as $key => $value) {
            if (!in_array($key, $this->hidden)) {
                $array[$key] = $this->applyCasts($key, $value);
            }
        }
        return $array;
    }
}
