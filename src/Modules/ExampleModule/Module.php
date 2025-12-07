<?php
namespace OOPress\Modules\ExampleModule;

use OOPress\Core\ModuleInterface;
use OOPress\Core\Router;

class Module implements ModuleInterface
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function register(): void
    {
        // Home page route
        $this->router->get('/example', function() {
            $controller = new Controller();
            $controller->home();
        });
    }
}
