<?php

namespace Axcel\AxcelCore\Routing;

use Axcel\Core\Bus\Container;
use Axcel\Core\Exceptions\RouteDispatchException;
use Axcel\Core\Http\Redirect;
use Axcel\Core\Http\Request;
use Axcel\Core\Http\Response;
use Axcel\Core\Middleware\EnsureCsrfMiddleware;
use Closure;
use Axcel\Core\Middleware\MiddlewareRegistry;
use ReflectionMethod;

class Router
{
    protected static array $routes = [];
    private static Container $container;
    private static ?ModelBindingService $modelBinding = null;

    // Single attribute to track the current group attributes
    protected static array $groupAttributes = [
        'middleware' => [],
        'controller' => null,
        'prefix' => '',
    ];

    // Stack to save and restore group state
    protected static array $groupStack = [];

    public function __construct(Container $container)
    {
        self::$container = $container;

        if (self::$modelBinding === null) {
            self::$modelBinding = new ModelBindingService($container);
        }
    }

    public static function load(string $file)
    {
        if (!file_exists($file)) {
            throw new \Exception("Route file not found: {$file}");
        }

        require $file;
    }

    public static function get(string $uri, callable|array|string $action)
    {
        return self::addRoute('GET', $uri, $action);
    }

    public static function post(string $uri, callable|array|string $action)
    {
        return self::addRoute('POST', $uri, $action);
    }

    public static function put(string $uri, callable|array|string $action)
    {
        return self::addRoute('PUT', $uri, $action);
    }

    public static function delete(string $uri, callable|array|string $action)
    {
        return self::addRoute('DELETE', $uri, $action);
    }

    public static function patch(string $uri, callable|array|string $action)
    {
        return self::addRoute('PATCH', $uri, $action);
    }

    public static function middleware(array|string $middleware): self
    {
        // Save current group state
        self::$groupStack[] = self::$groupAttributes;

        // Add middleware to current attributes
        $middlewares = is_string($middleware) ? [$middleware] : $middleware;
        self::$groupAttributes['middleware'] = array_merge(
            self::$groupAttributes['middleware'],
            $middlewares
        );

        return new static(self::$container);
    }

    public static function controller(string $controller): self
    {
        // Save current group state
        self::$groupStack[] = self::$groupAttributes;

        // Set controller
        self::$groupAttributes['controller'] = $controller;

        return new static(self::$container);
    }

    public static function prefix(string $prefix): self
    {
        // Save current group state
        self::$groupStack[] = self::$groupAttributes;

        // Build prefix by combining with existing prefix if any
        $currentPrefix = self::$groupAttributes['prefix'];
        if (!empty($currentPrefix)) {
            self::$groupAttributes['prefix'] = $currentPrefix . '/' . trim($prefix, '/');
        } else {
            self::$groupAttributes['prefix'] = trim($prefix, '/');
        }

        return new static(self::$container);
    }

    public static function group(Closure $callback)
    {
        // Execute the group callback
        call_user_func($callback);

        // Restore group attributes from stack
        if (!empty(self::$groupStack)) {
            self::$groupAttributes = array_pop(self::$groupStack);
        }
    }

    private static function addRoute(string $method, string $uri, callable|array|string $action)
    {
        // Apply prefix to URI if any
        $prefix = self::$groupAttributes['prefix'];
        if (!empty($prefix)) {
            $uri = $prefix . '/' . trim($uri, '/');
            $uri = '/' . trim($uri, '/');
        }

        // Process the action based on type and current controller
        $currentController = self::$groupAttributes['controller'];

        if (is_string($action) && $currentController) {
            // Action is a method name and we have a controller from group
            $action = [$currentController, $action];
        } elseif (is_string($action) && class_exists($action)) {
            // Action is an invokable controller class
            $action = ['_invokable' => $action];
        }

        // Apply middleware from group attributes
        $middlewares = self::$groupAttributes['middleware'];
        if (!empty($middlewares)) {
            if (is_array($action)) {
                if (isset($action['_middleware'])) {
                    $action['_middleware'] = array_merge($action['_middleware'], $middlewares);
                } else {
                    $action['_middleware'] = $middlewares;
                }
            } else {
                // Wrap the action to include middleware
                $action = [
                    '_action' => $action,
                    '_middleware' => $middlewares
                ];
            }
        }

        // Extract route parameters
        $parameters = [];
        if (preg_match_all('/{([^}]+)}/', $uri, $matches)) {
            $parameters = $matches[1];
        }

        // Convert URI to regex pattern
        $pattern = preg_replace('/{[^}]+}/', '([^/]+)', $uri);
        $pattern = '#^' . $pattern . '$#';

        // Store the route
        self::$routes[$method][$pattern] = [
            'action' => $action,
            'parameters' => $parameters,
            'uri' => $uri,
        ];

        return new static(self::$container);
    }

    public function dispatch(Request $request): Response
    {
        // dd(self::$routes);
        $path = $request->getPathInfo();
        $method = $request->getMethod();
        $route = $this->matchRoute($method, $path);

        if ($route === null) {
            return new Response("404 Not Found", 404);
        }

        [$action, $params] = $route;

        // Add route parameters to request
        foreach ($params as $key => $value) {
            $request->attributes->set($key, $value);
        }

        // Build middleware stack
        $middlewares = [];

        // Add method-specific middlewares (like CSRF)
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            $middlewares[] = new EnsureCsrfMiddleware();
        }

        // Add route-specific middlewares
        if (isset($action['_middleware']) && is_array($action['_middleware'])) {
            foreach ($action['_middleware'] as $middlewareName) {
                $middlewareClass = MiddlewareRegistry::get($middlewareName);
                if ($middlewareClass && class_exists($middlewareClass)) {
                    $middlewares[] = self::$container->make($middlewareClass);
                } else {
                    throw new \Exception("Middleware not found: {$middlewareName}");
                }
            }
        }

        // Create middleware chain
        $next = function (Request $request) use ($action, $params) {
            return $this->handleRoute($action, $request, $params);
        };

        foreach (array_reverse($middlewares) as $middleware) {
            $next = function (Request $request) use ($middleware, $next) {
                return $middleware->handle($request, $next);
            };
        }

        return $next($request);
    }

    private function handleRoute($action, Request $request, array $routeParams = []): Response
    {
        // Check if action has a wrapped action (from middleware)
        if (is_array($action) && isset($action['_action'])) {
            $action = $action['_action'];
        }

        // Handle closures
        if ($action instanceof Closure) {
            return $this->formatResponse(call_user_func($action, $request));
        }

        // Handle invokable controllers
        if (is_array($action) && isset($action['_invokable'])) {
            $controllerClass = $action['_invokable'];

            if (!class_exists($controllerClass) || !method_exists($controllerClass, '__invoke')) {
                throw new RouteDispatchException('__invoke', $controllerClass);
            }

            $instance = self::$container->make($controllerClass);
            $resolvedParams = $this->resolveMethodDependencies($controllerClass, '__invoke', $request, $routeParams);
            $response = call_user_func_array([$instance, '__invoke'], $resolvedParams);

            return $this->formatResponse($response);
        }

        // Handle controller@method format
        if (is_array($action)) {
            // Standard format [Controller::class, 'method']
            if (isset($action[0]) && isset($action[1]) && is_string($action[0]) && is_string($action[1])) {
                $controllerClass = $action[0];
                $actionMethod = $action[1];

                if (!class_exists($controllerClass) || !method_exists($controllerClass, $actionMethod)) {
                    throw new RouteDispatchException($actionMethod, $action);
                }

                $instance = self::$container->make($controllerClass);
                $resolvedParams = $this->resolveMethodDependencies($controllerClass, $actionMethod, $request, $routeParams);
                $response = call_user_func_array([$instance, $actionMethod], $resolvedParams);

                return $this->formatResponse($response);
            }
        }

        throw new RouteDispatchException('unknown', $action);
    }

    private function resolveMethodDependencies(string $controllerClass, string $method, Request $request, array $routeParams): array
    {
        $reflection = new ReflectionMethod($controllerClass, $method);
        $parameters = $reflection->getParameters();
        $resolvedParams = [];

        foreach ($parameters as $param) {
            $resolvedParams[] = self::$modelBinding->resolveParameter($param, $routeParams, $request);
        }

        return $resolvedParams;
    }

    private function matchRoute(string $method, string $path)
    {
        $routes = self::$routes[$method] ?? [];

        foreach ($routes as $pattern => $route) {
            if (preg_match($pattern, $path, $matches)) {
                // Remove full match
                array_shift($matches);

                $paramValues = [];

                // Pair parameter names with values
                foreach ($route['parameters'] as $index => $name) {
                    $paramValues[$name] = $matches[$index] ?? null;
                }

                return [$route['action'], $paramValues];
            }
        }

        return null;
    }

    private function formatResponse($response): Response
    {
        if ($response instanceof Response) {
            return $response;
        }

        if ($response instanceof Redirect) {
            return $response;
        }

        if (is_array($response)) {
            return Response::json($response);
        }

        if (is_string($response)) {
            return new Response($response, 200, ['Content-Type' => 'text/html']);
        }

        return new Response();
    }

    // Debug helper
    public static function getRoutes(): array
    {
        return self::$routes;
    }

    // Reset method for testing
    public static function reset(): void
    {
        self::$routes = [];
        self::$groupAttributes = [
            'middleware' => [],
            'controller' => null,
            'prefix' => '',
        ];
        self::$groupStack = [];
    }
}
