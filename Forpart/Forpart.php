<?php

namespace App\Core\Forpart;

use App\Core\Application;

abstract class Forpart
{
    protected static function getForpartAccessor()
    {
        throw new \RuntimeException('Forpart does not implement getForpartAccessor method.');
    }

    public static function __callStatic(string $method, array $arguments)
    {
        $instance = Application::getInstance()->make(static::getForpartAccessor());

        if (!$instance) {
            throw new \RuntimeException(sprintf(
                'A forpart root has not been set for %s',
                static::getForpartAccessor()
            ));
        }

        return $instance->$method(...$arguments);
    }
}
