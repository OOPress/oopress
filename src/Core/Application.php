<?php
declare(strict_types=1);

namespace OOPress\Core;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\EventDispatcher;
use OOPress\Core\Events\BeforeRequestEvent;
use OOPress\Core\Events\AfterRequestEvent;
use OOPress\Plugins\HelloWorld\HelloWorldPlugin;

class Application
{
    private EventDispatcher $dispatcher;

    public function __construct()
    {
        $this->dispatcher = new EventDispatcher();
        // Load modules, services, plugins here
        $this->bootModules();
        $this->bootPlugins();
    }

    public function handle(Request $request): Response
    {
        // Pre-request event
        $this->dispatcher->dispatch(new Events\BeforeRequestEvent($request));

        // Route dispatch (simplified example)
        $response = $this->dispatchRoute($request);

        // Post-request event
        $this->dispatcher->dispatch(new Events\AfterRequestEvent($request, $response));

        return $response;
    }

    private function bootModules(): void
    {
        // Initialize core modules (DB, Auth, Cache)
    }

    public function bootPlugins(): void
    {
        // Create plugin instance
        $plugin = new HelloWorldPlugin();

        // Register subscriber with EventDispatcher
        $this->dispatcher->addSubscriber($plugin);
    }

    private function dispatchRoute(Request $request): Response
    {
        // Minimal routing: check path and call controller
        $path = $request->getPathInfo();

        // Example: static response
        if ($path === '/hello') {
            return new Response('<h1>Hello from OOPress!</h1>');
        }

        return new Response('<h1>Page not found</h1>', 404);
    }
}
