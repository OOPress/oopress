<?php

declare(strict_types=1);

namespace OOPress;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use OOPress\Event\HookDispatcher;
use OOPress\Extension\ExtensionLoader;
use OOPress\Installer\Installer;
use OOPress\Migration\MigrationRunner;
use OOPress\Path\PathResolver;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasher;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\Yaml\Yaml;

/**
 * Kernel — The core of OOPress.
 * 
 * This class bootstraps the application, wires Symfony components,
 * loads configuration, and handles the request lifecycle.
 * 
 * @api
 */
class Kernel
{
    private const VERSION = '1.0.0-dev';
    private const ENVIRONMENT = 'prod'; // dev, prod, test
    
    private bool $booted = false;
    private ContainerInterface $container;
    private PathResolver $pathResolver;
    private Request $request;
    private HookDispatcher $hookDispatcher;
    private ExtensionLoader $extensionLoader;
    private Connection $connection;
    private MigrationRunner $migrationRunner;
    
    /**
     * @var array<string, mixed> Loaded configuration
     */
    private array $config = [];
    
    public function __construct(
        private readonly ?string $projectRoot = null,
        private readonly string $environment = self::ENVIRONMENT,
        private readonly bool $debug = false,
    ) {
        $this->pathResolver = new PathResolver($projectRoot);
    }
    
    /**
     * Get the kernel version.
     */
    public function getVersion(): string
    {
        return self::VERSION;
    }
    
    /**
     * Get the environment (dev, prod, test).
     */
    public function getEnvironment(): string
    {
        return $this->environment;
    }
    
    /**
     * Check if debug mode is enabled.
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }
    
    /**
     * Boot the kernel — load configuration, initialize services.
     */
    public function boot(): void
    {
        if ($this->booted) {
            return;
        }
        
        try {
            // Load configuration
            $this->loadConfiguration();
            
            // Initialize dependency injection container
            $this->initializeContainer();
            
            // Initialize event system
            $this->initializeEventSystem();
            
            // Initialize database connection
            $this->initializeDatabase();
            
            // Initialize extension loader
            $this->initializeExtensions();
            
            // Initialize migrations
            $this->initializeMigrations();
            
            // Register core event listeners
            $this->registerCoreListeners();
            
            $this->booted = true;
            
            // Dispatch kernel boot event
            $this->dispatchKernelEvent('kernel.boot');
            
        } catch (\Exception $e) {
            $this->handleBootException($e);
        }
    }
    
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request): Response
    {
        if (!$this->booted) {
            $this->boot();
        }
        
        $this->request = $request;
        
        try {
            // Check if installed
            if (!$this->isInstalled() && !$this->isInstallRequest($request)) {
                return $this->redirectToInstaller();
            }
            
            // Dispatch kernel request event
            $this->dispatchKernelEvent('kernel.request', ['request' => $request]);
            
            // Route the request
            $response = $this->handleRequest($request);
            
            // Dispatch kernel response event
            $this->dispatchKernelEvent('kernel.response', ['request' => $request, 'response' => $response]);
            
            return $response;
            
        } catch (\Exception $e) {
            return $this->handleException($e, $request);
        }
    }
    
    /**
     * Shutdown the kernel.
     */
    public function shutdown(): void
    {
        if (!$this->booted) {
            return;
        }
        
        // Dispatch kernel shutdown event
        $this->dispatchKernelEvent('kernel.shutdown');
        
        // Close database connection
        if ($this->connection && $this->connection->isConnected()) {
            $this->connection->close();
        }
        
        $this->booted = false;
    }
    
    /**
     * Get the service container.
     */
    public function getContainer(): ContainerInterface
    {
        if (!$this->booted) {
            throw new \RuntimeException('Kernel not booted');
        }
        
        return $this->container;
    }
    
    /**
     * Get the path resolver.
     */
    public function getPathResolver(): PathResolver
    {
        return $this->pathResolver;
    }
    
    /**
     * Get the hook dispatcher.
     */
    public function getHookDispatcher(): HookDispatcher
    {
        return $this->hookDispatcher;
    }
    
    /**
     * Get the extension loader.
     */
    public function getExtensionLoader(): ExtensionLoader
    {
        return $this->extensionLoader;
    }
    
    /**
     * Get the database connection.
     */
    public function getConnection(): Connection
    {
        return $this->connection;
    }
    
    /**
     * Load configuration from config/ directory.
     */
    private function loadConfiguration(): void
    {
        $configPath = $this->pathResolver->getConfigPath();
        
        // Load core configuration
        $coreConfig = $this->loadConfigFile($configPath . '/core.php');
        $databaseConfig = $this->loadConfigFile($configPath . '/database.php');
        $servicesConfig = $this->loadConfigFile($configPath . '/services.php');
        
        // Load settings.php (credentials)
        $settingsFile = $this->pathResolver->getSettingsFile();
        if (file_exists($settingsFile)) {
            $settings = require $settingsFile;
            if (is_array($settings)) {
                $this->config = array_merge($this->config, $settings);
            }
        }
        
        $this->config = array_merge(
            $this->config,
            $coreConfig ?? [],
            $databaseConfig ?? [],
            $servicesConfig ?? []
        );
    }
    
    /**
     * Load a configuration file.
     */
    private function loadConfigFile(string $path): ?array
    {
        if (!file_exists($path)) {
            return null;
        }
        
        $config = require $path;
        return is_array($config) ? $config : null;
    }
    
    /**
     * Initialize the dependency injection container.
     */
    private function initializeContainer(): void
    {
        $container = new ContainerBuilder();
        
        // Set kernel as a service
        $container->set('kernel', $this);
        $container->set(Kernel::class, $this);
        
        // Set path resolver
        $container->set('path_resolver', $this->pathResolver);
        $container->set(PathResolver::class, $this->pathResolver);
        
        // Load service definitions
        $servicesPath = $this->pathResolver->getConfigPath() . '/services.php';
        if (file_exists($servicesPath)) {
            $loader = new PhpFileLoader($container);
            $loader->load($servicesPath);
        }
        
        // Compile the container
        $container->compile();
        
        $this->container = $container;
    }
    
    /**
     * Initialize the event system.
     */
    private function initializeEventSystem(): void
    {
        // Create Symfony event dispatcher
        $eventDispatcher = new EventDispatcher();
        
        // Create OOPress hook dispatcher wrapper
        $this->hookDispatcher = new HookDispatcher($eventDispatcher);
        
        // Register in container
        if ($this->container->has('event_dispatcher')) {
            $this->container->set('event_dispatcher', $eventDispatcher);
        }
        if ($this->container->has('hook_dispatcher')) {
            $this->container->set('hook_dispatcher', $this->hookDispatcher);
        }
    }
    
    /**
     * Initialize database connection.
     */
    private function initializeDatabase(): void
    {
        if (!isset($this->config['database'])) {
            throw new \RuntimeException('Database configuration not found');
        }
        
        $dbConfig = $this->config['database'];
        
        // Add table prefix support
        $prefix = $dbConfig['table_prefix'] ?? 'oop_';
        $dbConfig['prefix'] = $prefix;
        
        try {
            $this->connection = DriverManager::getConnection($dbConfig);
            
            // Register in container
            if ($this->container->has('database_connection')) {
                $this->container->set('database_connection', $this->connection);
            }
            if ($this->container->has(Connection::class)) {
                $this->container->set(Connection::class, $this->connection);
            }
            
        } catch (\Exception $e) {
            throw new \RuntimeException(
                sprintf('Failed to connect to database: %s', $e->getMessage()),
                0,
                $e
            );
        }
    }
    
    /**
     * Initialize extension loader.
     */
    private function initializeExtensions(): void
    {
        $this->extensionLoader = new ExtensionLoader($this->pathResolver);
        
        // Register in container
        if ($this->container->has('extension_loader')) {
            $this->container->set('extension_loader', $this->extensionLoader);
        }
        if ($this->container->has(ExtensionLoader::class)) {
            $this->container->set(ExtensionLoader::class, $this->extensionLoader);
        }
        
        // Load module migrations
        foreach ($this->extensionLoader->getModules() as $moduleId => $module) {
            if ($this->migrationRunner) {
                $namespace = $this->getModuleMigrationNamespace($moduleId);
                $this->migrationRunner->registerModuleMigrations($moduleId, $namespace);
            }
        }
    }
    
    /**
     * Initialize migrations.
     */
    private function initializeMigrations(): void
    {
        if (!$this->connection) {
            return;
        }
        
        $this->migrationRunner = new MigrationRunner(
            $this->connection,
            $this->pathResolver
        );
        
        // Register in container
        if ($this->container->has('migration_runner')) {
            $this->container->set('migration_runner', $this->migrationRunner);
        }
        if ($this->container->has(MigrationRunner::class)) {
            $this->container->set(MigrationRunner::class, $this->migrationRunner);
        }
    }
    
    /**
     * Register core event listeners.
     */
    private function registerCoreListeners(): void
    {
        // Register listeners from configuration
        if (isset($this->config['listeners']) && is_array($this->config['listeners'])) {
            foreach ($this->config['listeners'] as $eventName => $listeners) {
                foreach ($listeners as $listener) {
                    $this->hookDispatcher->addListener($eventName, $listener);
                }
            }
        }
        
        // Register module subscribers
        foreach ($this->extensionLoader->getModules() as $moduleId => $module) {
            $this->registerModuleSubscribers($moduleId, $module);
        }
    }
    
    /**
     * Register event subscribers from a module.
     */
    private function registerModuleSubscribers(string $moduleId, Extension\ExtensionManifest $module): void
    {
        // This will be expanded when we have proper module loading
        // For now, it's a placeholder for module event subscribers
        $subscriberClass = $this->getModuleSubscriberClass($moduleId);
        
        if ($subscriberClass && class_exists($subscriberClass)) {
            $subscriber = new $subscriberClass();
            if ($subscriber instanceof Event\HookSubscriberInterface) {
                // Register with the underlying Symfony dispatcher
                $eventDispatcher = $this->hookDispatcher->getDispatcher();
                $eventDispatcher->addSubscriber($subscriber);
            }
        }
    }
    
    /**
     * Get the module migration namespace.
     */
    private function getModuleMigrationNamespace(string $moduleId): string
    {
        // Convert oopress/users -> OOPress\Module\Users\Migrations
        $parts = explode('/', $moduleId);
        $vendor = ucfirst($parts[0]);
        $module = implode('', array_map('ucfirst', explode('-', $parts[1])));
        
        return sprintf('OOPress\\Module\\%s\\Migrations', $module);
    }
    
    /**
     * Get the module subscriber class.
     */
    private function getModuleSubscriberClass(string $moduleId): ?string
    {
        $parts = explode('/', $moduleId);
        $vendor = ucfirst($parts[0]);
        $module = implode('', array_map('ucfirst', explode('-', $parts[1])));
        
        $class = sprintf('OOPress\\Module\\%s\\EventSubscriber', $module);
        
        return class_exists($class) ? $class : null;
    }
    
    /**
     * Check if OOPress is installed.
     */
    private function isInstalled(): bool
    {
        // Check if settings.php exists with database config
        $settingsFile = $this->pathResolver->getSettingsFile();
        
        if (!file_exists($settingsFile)) {
            return false;
        }
        
        // Check if migrations have been run
        try {
            if ($this->connection && $this->connection->isConnected()) {
                $schemaManager = $this->connection->createSchemaManager();
                return $schemaManager->tablesExist(['oop_migrations']);
            }
        } catch (\Exception $e) {
            // Connection failed
            return false;
        }
        
        return false;
    }
    
    /**
     * Check if this is an installation request.
     */
    private function isInstallRequest(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/install');
    }
    
    /**
     * Redirect to the installer.
     */
    private function redirectToInstaller(): Response
    {
        return new Response(
            '<!DOCTYPE html><html><head><title>Installing OOPress...</title>'
            . '<meta http-equiv="refresh" content="0;url=/install">'
            . '</head><body>Redirecting to installer...</body></html>',
            Response::HTTP_FOUND,
            ['Location' => '/install']
        );
    }
    
    /**
     * Handle a routed request.
     */
    private function handleRequest(Request $request): Response
    {
        // Create router
        $routes = $this->loadRoutes();
        $context = new RequestContext();
        $context->fromRequest($request);
        
        $router = new Router(new RouteCollection(), $context);
        
        // This is a simplified routing implementation
        // In a full implementation, we'd use Symfony's HttpKernel component
        // For now, we'll match routes manually
        
        try {
            $parameters = $router->match($request->getPathInfo());
            $controller = $parameters['_controller'] ?? null;
            
            if (!$controller) {
                throw new \Exception('No controller found');
            }
            
            // Dispatch controller event
            $this->dispatchKernelEvent('kernel.controller', [
                'controller' => $controller,
                'parameters' => $parameters,
            ]);
            
            // Execute controller
            $response = $this->executeController($controller, $parameters, $request);
            
            return $response;
            
        } catch (\Exception $e) {
            return $this->handleRoutingException($e);
        }
    }
    
    /**
     * Load routes from all modules.
     */
    private function loadRoutes(): RouteCollection
    {
        $collection = new RouteCollection();
        
        // Load core routes
        $coreRoutesPath = $this->pathResolver->getCorePath() . '/routes.php';
        if (file_exists($coreRoutesPath)) {
            $routes = require $coreRoutesPath;
            if ($routes instanceof RouteCollection) {
                $collection->addCollection($routes);
            }
        }
        
        // Load module routes
        foreach ($this->extensionLoader->getModules() as $moduleId => $module) {
            $routesPath = $this->pathResolver->getModulePath($moduleId) . '/routes.php';
            if (file_exists($routesPath)) {
                $routes = require $routesPath;
                if ($routes instanceof RouteCollection) {
                    $collection->addCollection($routes);
                }
            }
        }
        
        return $collection;
    }
    
    /**
     * Execute a controller.
     */
    private function executeController(callable|string $controller, array $parameters, Request $request): Response
    {
        if (is_string($controller)) {
            // Parse controller: "App\Controller\HomeController::index"
            $parts = explode('::', $controller);
            if (count($parts) === 2) {
                $class = $parts[0];
                $method = $parts[1];
                
                if (!class_exists($class)) {
                    throw new \RuntimeException(sprintf('Controller class not found: %s', $class));
                }
                
                $controllerInstance = new $class();
                
                if (!method_exists($controllerInstance, $method)) {
                    throw new \RuntimeException(sprintf('Controller method not found: %s::%s', $class, $method));
                }
                
                $controller = [$controllerInstance, $method];
            }
        }
        
        // Call the controller
        $response = call_user_func_array($controller, array_merge([$request], $parameters));
        
        if (!$response instanceof Response) {
            throw new \RuntimeException('Controller must return a Response object');
        }
        
        return $response;
    }
    
    /**
     * Handle routing exceptions.
     */
    private function handleRoutingException(\Exception $e): Response
    {
        if ($e instanceof \Symfony\Component\Routing\Exception\ResourceNotFoundException) {
            return new Response('Page not found', Response::HTTP_NOT_FOUND);
        }
        
        if ($e instanceof \Symfony\Component\Routing\Exception\MethodNotAllowedException) {
            return new Response('Method not allowed', Response::HTTP_METHOD_NOT_ALLOWED);
        }
        
        return $this->handleException($e, $this->request);
    }
    
    /**
     * Handle an exception.
     */
    private function handleException(\Exception $e, Request $request): Response
    {
        // Dispatch kernel exception event
        $this->dispatchKernelEvent('kernel.exception', [
            'exception' => $e,
            'request' => $request,
        ]);
        
        if ($this->debug) {
            return new Response(
                sprintf(
                    '<h1>Error</h1><pre>%s</pre><h2>Stack trace</h2><pre>%s</pre>',
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
        
        return new Response('An error occurred', Response::HTTP_INTERNAL_SERVER_ERROR);
    }
    
    /**
     * Handle boot exception.
     */
    private function handleBootException(\Exception $e): void
    {
        if ($this->debug) {
            throw $e;
        }
        
        // Log the error (when we have logging)
        error_log(sprintf('OOPress boot failed: %s', $e->getMessage()));
        
        // Display user-friendly error
        if (php_sapi_name() === 'cli') {
            echo "Error: " . $e->getMessage() . "\n";
            exit(1);
        }
        
        http_response_code(500);
        echo '<h1>OOPress Boot Error</h1>';
        echo '<p>The application could not start. Please check the logs.</p>';
        exit;
    }
    
    /**
     * Dispatch a kernel event.
     */
    private function dispatchKernelEvent(string $eventName, array $context = []): void
    {
        if (!isset($this->hookDispatcher)) {
            return;
        }
        
        $event = new Event\Event();
        $event->setContext($context);
        
        $this->hookDispatcher->dispatch($event, $eventName);
    }
}
