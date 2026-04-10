<?php

declare(strict_types=1);

namespace OOPress\Core;

use Psr\Container\ContainerInterface;
use ReflectionClass;
use ReflectionParameter;
use InvalidArgumentException;

class Container implements ContainerInterface
{
    private array $bindings = [];
    private array $instances = [];
    private array $aliases = [];
    
    /**
     * Bind an abstract to a concrete implementation
     */
    public function bind(string $abstract, callable|string|null $concrete = null): void
    {
        if ($concrete === null) {
            $concrete = $abstract;
        }
        
        $this->bindings[$abstract] = $this->normalizeConcrete($concrete);
    }
    
    /**
     * Bind a singleton (shared instance)
     */
    public function singleton(string $abstract, callable|string|null $concrete = null): void
    {
        $this->bind($abstract, $concrete);
        $this->instances[$abstract] = null; // Mark for lazy singleton
    }
    
    /**
     * Register an already instantiated instance as a singleton
     */
    public function instance(string $abstract, object $instance): void
    {
        $this->instances[$abstract] = $instance;
        $this->bindings[$abstract] = fn() => $instance;
    }

    /**
     * Alias an abstract to another name
     */
    public function alias(string $abstract, string $alias): void
    {
        $this->aliases[$alias] = $abstract;
    }
    
    /**
     * Resolve an instance from the container
     */
    public function get(string $id): mixed
    {
        $id = $this->resolveAlias($id);
        
        // Return existing singleton instance
        if (isset($this->instances[$id]) && $this->instances[$id] !== null) {
            return $this->instances[$id];
        }
        
        // Resolve new instance
        $instance = $this->resolve($id);
        
        // Store if singleton
        if (array_key_exists($id, $this->instances)) {
            $this->instances[$id] = $instance;
        }
        
        return $instance;
    }
    
    /**
     * Alias for get() - creates/retrieves an instance
     */
    public function make(string $abstract, array $parameters = []): object
    {
        // If parameters are provided, build manually without using singleton cache
        if (!empty($parameters)) {
            return $this->buildWithParameters($abstract, $parameters);
        }
        
        return $this->get($abstract);
    }
    
    /**
     * Check if container has binding
     */
    public function has(string $id): bool
    {
        $id = $this->resolveAlias($id);
        return isset($this->bindings[$id]) || class_exists($id);
    }
    
    /**
     * Resolve a binding or class
     */
    private function resolve(string $abstract): mixed
    {
        if (!isset($this->bindings[$abstract])) {
            return $this->autowire($abstract);
        }
        
        $concrete = $this->bindings[$abstract];
        
        if (is_callable($concrete)) {
            return $concrete($this);
        }
        
        if (is_string($concrete)) {
            return $this->resolve($concrete);
        }
        
        return $this->autowire($concrete);
    }
    
    /**
     * Automatically resolve class dependencies via reflection
     */
    private function autowire(string $class): object
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class {$class} not found");
        }
        
        $reflection = new ReflectionClass($class);
        
        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException("Class {$class} is not instantiable");
        }
        
        $constructor = $reflection->getConstructor();
        
        if ($constructor === null) {
            return new $class();
        }
        
        $parameters = $constructor->getParameters();
        $dependencies = $this->resolveDependencies($parameters);
        
        return $reflection->newInstanceArgs($dependencies);
    }
    
    /**
     * Resolve constructor dependencies
     */
    private function resolveDependencies(array $parameters): array
    {
        $dependencies = [];
        
        foreach ($parameters as $parameter) {
            $type = $parameter->getType();
            
            if ($type === null || $type->isBuiltin()) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } elseif ($parameter->allowsNull()) {
                    $dependencies[] = null;
                } else {
                    throw new InvalidArgumentException("Cannot resolve parameter: {$parameter->getName()}");
                }
                continue;
            }
            
            $typeName = $type->getName();
            if ($this->has($typeName)) {
                $dependencies[] = $this->get($typeName);
            } elseif ($type->allowsNull()) {
                $dependencies[] = null;
            } else {
                throw new InvalidArgumentException("Cannot resolve dependency: {$typeName}");
            }
        }
        
        return $dependencies;
    }
    
    /**
     * Build a class with custom parameters (no caching)
     */
    private function buildWithParameters(string $class, array $parameters): object
    {
        if (!class_exists($class)) {
            throw new InvalidArgumentException("Class {$class} not found");
        }
        
        $reflection = new ReflectionClass($class);
        
        if (!$reflection->isInstantiable()) {
            throw new InvalidArgumentException("Class {$class} is not instantiable");
        }
        
        return $reflection->newInstanceArgs($parameters);
    }
    
    /**
     * Normalize concrete definition
     */
    private function normalizeConcrete(callable|string $concrete): callable|string
    {
        if (is_string($concrete) && class_exists($concrete)) {
            return $concrete;
        }
        
        return $concrete;
    }
    
    /**
     * Resolve alias to actual abstract
     */
    private function resolveAlias(string $id): string
    {
        return $this->aliases[$id] ?? $id;
    }
}