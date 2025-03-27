<?php

namespace App\Core\Forpart;

/**
 * Class Route
 *
 * This class serves as a static proxy to the router service, allowing
 * easy access to routing methods, including middleware handling for routes.
 *
 * @package App\Core\Forpart
 *
 * @method static void get(string $uri, callable|array|string $action) Register a GET route
 * @method static void post(string $uri, callable|array|string $action) Register a POST route
 * @method static void put(string $uri, callable|array|string $action) Register a PUT route
 * @method static void delete(string $uri, callable|array|string $action) Register a DELETE route
 * @method static void patch(string $uri, callable|array|string $action) Register a PATCH route
 * @method static void load(string $file) Load route definitions from a file
 * @method static self controller(string $controller) Define a controller group for multiple routes
 * @method static self group(callable $routes) Define a group of routes within a closure
 * @method static self middleware(array $middlewares) Attach middleware to a group of routes
 */
class Route extends Forpart
{
    /**
     * Get the accessor name for the router service.
     *
     * @return string
     */
    protected static function getForpartAccessor()
    {
        return 'router';
    }
}
