<?php

namespace Axcel\AxcelCore\Bus;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use Exception;

class Container implements ContainerInterface
{
    protected array $bindings = [];
    protected array $instances = [];

    public function bind(string $key, callable|string $resolver, bool $singleton = false)
    {
        $this->bindings[$key] = ['resolver' => $resolver, 'singleton' => $singleton];
    }

    public function make(string $key)
    {
        // Check if the binding exists
        if (isset($this->bindings[$key])) {
            $binding = $this->bindings[$key];

            // Return singleton instance if available
            if ($binding['singleton'] && isset($this->instances[$key])) {
                return $this->instances[$key];
            }

            $resolver = $binding['resolver'];
            $instance = is_callable($resolver) ? $resolver($this) : new $resolver();

            if ($binding['singleton']) {
                $this->instances[$key] = $instance;
            }

            return $instance;
        }

        // Attempt automatic resolution
        return $this->resolveAutomatically($key);
    }

    public function get(string $id)
    {
        if (!$this->has($id)) {
            throw new Exception("No entry found for {$id}");
        }

        return $this->make($id);
    }

    public function has(string $id): bool
    {
        return isset($this->bindings[$id]) || class_exists($id);
    }

    private function resolveAutomatically(string $class)
    {
        if (!class_exists($class)) {
            throw new \Exception("Class {$class} not found in container.");
        }

        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (!$constructor) {
            return new $class();
        }

        $dependencies = array_map(function (ReflectionParameter $param) {
            $type = $param->getType();

            if ($type && !$type->isBuiltin()) {
                return $this->make($type->getName());
            }

            if ($param->isDefaultValueAvailable()) {
                return $param->getDefaultValue();
            }

            throw new \Exception("Cannot resolve parameter {$param->getName()} for class {$param->getDeclaringClass()->getName()}");
        }, $constructor->getParameters());

        return $reflection->newInstanceArgs($dependencies);
    }
}
