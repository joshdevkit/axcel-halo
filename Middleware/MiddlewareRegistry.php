<?php

namespace Axcel\AxcelCore\Middleware;

class MiddlewareRegistry
{
    private static array $middlewares = [];

    public static function register(string $name, string $class)
    {
        self::$middlewares[$name] = $class;
    }

    public static function get(string $name): ?string
    {
        return self::$middlewares[$name] ?? null;
    }
}
