<?php

declare(strict_types=1);

namespace OOPress\Core;

use FastRoute\RouteCollector;
use FastRoute\Dispatcher;
use OOPress\Http\Request;
use OOPress\Http\Response;
use OOPress\Http\MiddlewareInterface;
use RuntimeException;

use function FastRoute\simpleDispatcher;

class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private ?Dispatcher $dispatcher = null;
    private Container $container;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    /**
     * Add a route directly (useful for array-based loading)
     */
    public function addRoute(string|array $methods, string $path, callable|array $handler, array $middlewares = []): void
    {
        $methods = (array) $methods;
        
        foreach ($methods as $method) {
            $this->routes[] = [
                'method' => $method,
                'path' => $path,
                'handler' => $handler,
                'middlewares' => $middlewares
            ];
        }
    }
    
    /**
     * Register multiple routes from an array
     */
    public function addRoutes(array $routes): void
    {
        foreach ($routes as $method => $methodRoutes) {
            foreach ($methodRoutes as $path => $config) {
                // Handle both simple handler arrays and full config arrays
                if (is_array($config) && isset($config['handler'])) {
                    $handler = $config['handler'];
                    $middlewares = $config['middlewares'] ?? [];
                } else {
                    $handler = $config;
                    $middlewares = [];
                }
                
                $this->addRoute($method, $path, $handler, $middlewares);
            }
        }
    }
    
    /**
     * Register a GET route
     */
    public function get(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('GET', $path, $handler, $middlewares);
    }
    
    /**
     * Register a POST route
     */
    public function post(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('POST', $path, $handler, $middlewares);
    }
    
    /**
     * Register a PUT route
     */
    public function put(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middlewares);
    }
    
    /**
     * Register a DELETE route
     */
    public function delete(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middlewares);
    }
    
    /**
     * Register a route for multiple methods
     */
    public function any(string $path, callable|array $handler, array $middlewares = []): void
    {
        $this->addRoute(['GET', 'POST', 'PUT', 'DELETE', 'PATCH'], $path, $handler, $middlewares);
    }
    
    /**
     * Group routes with common middleware
     */
    public function group(string $prefix, callable $callback, array $middlewares = []): void
    {
        $previousGroup = $this->currentGroup ?? null;
        $this->currentGroup = [
            'prefix' => ($previousGroup['prefix'] ?? '') . $prefix,
            'middlewares' => array_merge($previousGroup['middlewares'] ?? [], $middlewares)
        ];
        
        $callback($this);
        
        $this->currentGroup = $previousGroup;
    }
    
    /**
     * Dispatch a request and get response
     */
    public function dispatch(Request $request): Response
    {
        $dispatcher = $this->getDispatcher();
        $routeInfo = $dispatcher->dispatch($request->method(), $request->path());
        
        switch ($routeInfo[0]) {
            case Dispatcher::NOT_FOUND:
                return new Response('404 - Page Not Found', 404);
                
            case Dispatcher::METHOD_NOT_ALLOWED:
                return new Response('405 - Method Not Allowed', 405);
                
            case Dispatcher::FOUND:
                $handler = $routeInfo[1];
                $vars = $routeInfo[2];
                
                // Add route parameters to request
                foreach ($vars as $key => $value) {
                    $request = $request->withAttribute($key, $value);
                }
                
                return $this->callHandler($handler, $request, $this->getMiddlewaresForRoute($handler));
        }
        
        return new Response('500 - Internal Server Error', 500);
    }
    
    /**
     * Call handler with middleware pipeline
     */
    private function callHandler($handler, Request $request, array $middlewares = []): Response
    {
        $pipeline = function(Request $request) use ($handler) {
            return $this->executeHandler($handler, $request);
        };
        
        // Apply middlewares in reverse order (LIFO)
        while ($middleware = array_pop($middlewares)) {
            $pipeline = function(Request $request) use ($middleware, $pipeline) {
                $middlewareInstance = $this->resolveMiddleware($middleware);
                return $middlewareInstance->process($request, $pipeline);
            };
        }
        
        return $pipeline($request);
    }
    
    /**
     * Execute the actual route handler
     */
    private function executeHandler($handler, Request $request): Response
    {
        if (is_callable($handler)) {
            $result = $handler($request);
        } elseif (is_array($handler) && count($handler) === 2) {
            $controller = $this->container->make($handler[0]);
            $method = $handler[1];
            
            // Check if method exists
            if (!method_exists($controller, $method)) {
                throw new RuntimeException("Method {$method} not found in " . get_class($controller));
            }
            
            $result = $controller->$method($request);
        } else {
            throw new RuntimeException('Invalid route handler');
        }
        
        // Convert string response to Response object
        if (is_string($result)) {
            return new Response($result);
        }
        
        if (!$result instanceof Response) {
            throw new RuntimeException('Handler must return Response object or string');
        }
        
        return $result;
    }
    
    /**
     * Get middlewares for a specific route
     */
    private function getMiddlewaresForRoute($handler): array
    {
        foreach ($this->routes as $route) {
            if ($route['handler'] === $handler) {
                return $route['middlewares'];
            }
        }
        return [];
    }
    
    /**
     * Resolve middleware from container or class name
     */
    private function resolveMiddleware(string $class): MiddlewareInterface
    {
        // Debug: Log what we're looking for
        error_log("Looking for middleware: " . $class);
        
        // Check if class exists
        if (!class_exists($class)) {
            error_log("Class does not exist: " . $class);
            throw new RuntimeException("Middleware class {$class} does not exist");
        }
        
        // Try to instantiate directly (simplest approach)
        try {
            $middleware = new $class();
            if ($middleware instanceof MiddlewareInterface) {
                return $middleware;
            }
            error_log("Class {$class} does not implement MiddlewareInterface");
        } catch (\Exception $e) {
            error_log("Failed to instantiate {$class}: " . $e->getMessage());
        }
        
        throw new RuntimeException("Middleware {$class} not found");
    }
        
    /**
     * Get or create FastRoute dispatcher
     */
    private function getDispatcher(): Dispatcher
    {
        if ($this->dispatcher === null) {
            $this->dispatcher = simpleDispatcher(function(RouteCollector $r) {
                foreach ($this->routes as $route) {
                    $r->addRoute($route['method'], $route['path'], $route['handler']);
                }
            });
        }
        
        return $this->dispatcher;
    }
}