<?php

namespace Axcel\AxcelCore\Routing;

use Axcel\AxcelCore\Bus\Container;
use ReflectionParameter;

class ModelBindingService
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Resolve a model based on its class name and identifier
     */
    public function resolveModel(string $modelClass, mixed $routeValue): ?object
    {
        // Ensure the model class exists
        if (!class_exists($modelClass)) {
            throw new \Exception("Model class not found: {$modelClass}");
        }

        // Check if the model has a find method (standard for model classes)
        if (method_exists($modelClass, 'find')) {
            // Use the standard find method
            $model = $modelClass::find($routeValue);
            if ($model === null) {
                throw new \Exception("Model not found with ID: {$routeValue}");
            }
            return $model;
        }

        // Check if the model has a findBy method (for custom queries)
        if (method_exists($modelClass, 'findBy')) {
            // Automatically determine the key field to search by (if needed)
            $keyField = $this->getModelKeyField($modelClass);

            // Call the findBy method with the dynamic field and value
            $model = call_user_func([$modelClass, 'findBy'], $keyField, $routeValue);

            if ($model === null) {
                throw new \Exception("Model not found with {$keyField}: {$routeValue}");
            }
            return $model;
        }

        // Default to using find if neither find nor findBy exists
        return $modelClass::find($routeValue);
    }


    /**
     * Try to determine the key field for a model
     */
    private function getModelKeyField(string $modelClass): string
    {
        if (defined("$modelClass::KEY_FIELD")) {
            return $modelClass::KEY_FIELD;
        }

        return 'id';
    }

    /**
     * Resolve a parameter based on its type hint, name, and available route parameters
     */
    public function resolveParameter(ReflectionParameter $param, array $routeParams, $request = null): mixed
    {
        $paramType = $param->getType();
        $paramName = $param->getName();

        // If parameter name exists in route parameters, try to resolve it as a model
        if (isset($routeParams[$paramName]) && $paramType && !$paramType->isBuiltin()) {
            return $this->resolveModel($paramType->getName(), $routeParams[$paramName]);
        }

        // If parameter is in route parameters but has no type hint or is a builtin type
        if (isset($routeParams[$paramName])) {
            return $routeParams[$paramName];
        }

        if ($request && $paramType && is_a($request, $paramType->getName(), true)) {
            return $request;
        }

        // If it's a class type but not a route parameter, resolve from container
        if ($paramType && !$paramType->isBuiltin()) {
            return $this->container->make($paramType->getName());
        }

        // If parameter has a default value, use it
        if ($param->isDefaultValueAvailable()) {
            return $param->getDefaultValue();
        }

        // If we reach here, we can't resolve the parameter
        throw new \Exception("Unable to resolve parameter: {$paramName}");
    }
}
