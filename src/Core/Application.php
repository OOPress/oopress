<?php
namespace OOPress\Core;

use OOPress\Core\Config;

class Application
{
    private Router $router;

    public function __construct()
    {
        Config::load(__DIR__ . '/../../config');
        $this->router = new Router();
        $this->registerRoutes();
    }

    private array $modules = [];

    private function loadModules(): void
    {
        // Example: automatically load ExampleModule
        $exampleModuleClass = \OOPress\Modules\ExampleModule\Module::class;
        $module = new $exampleModuleClass($this->router);
        $module->register();
        $this->modules[] = $module;
    }

    private function registerRoutes(): void
    {
        // Core home route
        $this->router->get('/', function() {
            $this->renderHome();
        });

        // Load all modules
        $this->loadModules();

        // Example about page
        $this->router->get('/about', function() {
            echo "<h1>About OOPress</h1><p>This is a minimal CMS framework.</p>";
        });

        $this->router->get('/testconfig', function() {
            echo "App Name: " . config('app.name');
            echo "<br>DB User: " . config('database.username');
        });

        $this->router->get('/test', function($req, $res) {
            $res->html("<h1>Method: {$req->method()}</h1>");
        });

    }
    
    public function run(): void
    {
        echo "Hello World — OOPress development has started!\n";
    }

    public function runWeb(): void
    {
        $this->router->dispatch();
    }

    private function renderHome(): void
    {
        $logoBasePath = '/assets/images/logo';
        $logoLight = $logoBasePath . '/oopress_logo.png';
        $logoDark  = $logoBasePath . '/oopress_logo_dark.png';

        echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>OOPress - Home</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 50px; background-color: #f5f5f5; color: #333; }
        img.logo { width: 300px; height: auto; }
        h1 { color: #FF9500; }
        p { font-size: 1.2em; }
        @media (prefers-color-scheme: dark) {
            body { background-color: #1e1e1e; color: #f5f5f5; }
            img.logo { content: url("$logoDark"); }
            h1 { color: #FFA500; }
        }
    </style>
</head>
<body>
    <img class="logo" src="$logoLight" alt="OOPress Logo">
    <h1>Hello World!</h1>
    <p>OOPress development has started.</p>
    <p><a href="/about">About Page</a></p>
</body>
</html>
HTML;
    }
}
