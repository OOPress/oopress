<?php

declare(strict_types=1);

namespace OOPress\Core;

use OOPress\Http\Request;
use OOPress\Http\Response;

class Application
{
    private Container $container;
    private EventBus $events;
    private Router $router;
    private string $basePath;
    private array $config = [];
    
    public function __construct(string $basePath)
    {
        $this->basePath = $basePath;
        $this->container = new Container();
        $this->events = new EventBus();
        
        $this->registerBaseBindings();
    }
    
    /**
     * Register core bindings in container
     */
    /*private function registerBaseBindings(): void
    {
        $this->container->singleton(Application::class, $this);
        $this->container->singleton(Container::class, $this->container);
        $this->container->singleton(EventBus::class, $this->events);
        $this->container->singleton(Router::class, function($c) {
            return new Router($c);
        });
    }*/
    
    /**
     * Register core bindings in container
     */
    private function registerBaseBindings(): void
    {
        $this->container->singleton(Application::class, fn() => $this);  // ← Fix #1
        $this->container->singleton(Container::class, fn() => $this->container);  // ← Fix #2
        $this->container->singleton(EventBus::class, fn() => $this->events);  // ← Fix #3
        $this->container->singleton(Router::class, function($c) {
            return new Router($c);
        });
    }

    /**
     * Load configuration files
     */
    public function loadConfig(string $configPath): void
    {
        $files = glob($configPath . '/*.php');
        foreach ($files as $file) {
            $name = basename($file, '.php');
            $this->config[$name] = require $file;
        }
    }
    
    /**
     * Load routes from file
     */
    public function loadRoutes(string $routesFile): void
    {
        $router = $this->container->get(Router::class);
        require $routesFile;
    }
    
    /**
     * Register service providers
     */
    public function registerProviders(array $providers): void
    {
        foreach ($providers as $provider) {
            $instance = $this->container->make($provider);
            if (method_exists($instance, 'register')) {
                $instance->register($this->container);
            }
            if (method_exists($instance, 'boot')) {
                $instance->boot($this);
            }
        }
    }
    
    /**
     * Handle incoming request
     */
    public function handle(Request $request): Response
    {
        $this->events->dispatch(new Events\ApplicationStarted($request));
        
        $router = $this->container->get(Router::class);
        $response = $router->dispatch($request);
        
        $this->events->dispatch(new Events\ApplicationFinished($response));
        
        return $response;
    }
    
    /**
     * Run the application
     */
    public function run(Request $request): void
    {
        $response = $this->handle($request);
        $response->send();
    }
    
    /**
     * Get container instance
     */
    public function getContainer(): Container
    {
        return $this->container;
    }
    
    /**
     * Get config value
     */
    public function config(string $key, $default = null)
    {
        $parts = explode('.', $key);
        $value = $this->config;
        
        foreach ($parts as $part) {
            if (!isset($value[$part])) {
                return $default;
            }
            $value = $value[$part];
        }
        
        return $value;
    }
    
    /**
     * Get base path
     */
    public function basePath(string $path = ''): string
    {
        return $this->basePath . ($path ? DIRECTORY_SEPARATOR . $path : '');
    }
}