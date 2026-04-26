<?php

declare(strict_types=1);

use OOPress\Core\Application;
use OOPress\Core\Router;
use OOPress\Core\Database\Model;
use Medoo\Medoo;
use OOPress\Core\I18n\Translator;
use OOPress\Http\Middleware\LanguageMiddleware;
use OOPress\Core\Session;
use OOPress\Core\Auth;
use OOPress\Http\Middleware\AdminMiddleware;
use OOPress\Http\Middleware\AuthMiddleware;
use OOPress\Http\Middleware\GuestMiddleware;
use OOPress\Core\Plugin\PluginManager;

// Check if installer is running - skip everything else
$requestUri = $_SERVER['REQUEST_URI'] ?? '';
$isInstalling = strpos($requestUri, '/install') === 0;

if ($isInstalling) {
    // Load minimal setup for installer
    require __DIR__ . '/../vendor/autoload.php';
    
    // Start session for installer
    session_start();
    
    // Load installer controller
    $router = new OOPress\Core\Router(new OOPress\Core\Container());
    
    // Only register install routes
    $router->get('/install', [OOPress\Controllers\InstallController::class, 'welcome']);
    $router->get('/install/welcome', [OOPress\Controllers\InstallController::class, 'welcome']);
    $router->get('/install/database', [OOPress\Controllers\InstallController::class, 'database']);
    $router->post('/install/database', [OOPress\Controllers\InstallController::class, 'database']);
    $router->get('/install/admin', [OOPress\Controllers\InstallController::class, 'admin']);
    $router->post('/install/admin', [OOPress\Controllers\InstallController::class, 'admin']);
    $router->get('/install/site', [OOPress\Controllers\InstallController::class, 'site']);
    $router->post('/install/site', [OOPress\Controllers\InstallController::class, 'site']);
    $router->get('/install/run', [OOPress\Controllers\InstallController::class, 'run']);
    
    $request = OOPress\Http\Request::fromGlobals();
    $response = $router->dispatch($request);
    $response->send();
    exit;
}

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/Core/helpers.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Error handling
$whoops = new Whoops\Run();
if ($_ENV['APP_DEBUG'] ?? true) {
    $whoops->pushHandler(new Whoops\Handler\PrettyPageHandler());
} else {
    $whoops->pushHandler(new Whoops\Handler\PlainTextHandler());
}
$whoops->register();

// Create application
$app = new Application(__DIR__ . '/..');

// Create translator
$translator = new Translator($_ENV['APP_LOCALE'] ?? 'en');
$translator->setFallbackLocale($_ENV['APP_FALLBACK_LOCALE'] ?? 'en');

// Make translator available globally
global $translator;
$GLOBALS['translator'] = $translator;

// Bind to container
$app->getContainer()->singleton(Translator::class, fn() => $translator);
$app->getContainer()->singleton(LanguageMiddleware::class);

// Load helper functions
require __DIR__ . '/../src/Core/I18n/helpers.php';

// Load default translations
$translator->load('default');

// Load configuration
$app->loadConfig(__DIR__ . '/../config');

// Setup database only if .env exists with required DB config
if (file_exists(__DIR__ . '/../.env') && isset($_ENV['DB_HOST']) && isset($_ENV['DB_NAME'])) {
    $db = new Medoo([
        'type' => $_ENV['DB_TYPE'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'],
        'database' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'port' => (int)($_ENV['DB_PORT'] ?? 3306),
    ]);
    
    Model::setDB($db);
    
    // Bind database to container
    $app->getContainer()->singleton(Medoo::class, fn() => $db);
}

// Load plugin system only if database is available
if (file_exists(__DIR__ . '/../.env') && isset($_ENV['DB_HOST']) && isset($_ENV['DB_NAME'])) {
    $pluginManager = new PluginManager();
    $pluginManager->loadActivePluginsFromDB();
    
    // Store in container
    $app->getContainer()->singleton(PluginManager::class, fn() => $pluginManager);
}

// Initialize session and auth
$session = new Session();
$auth = new Auth($session);

// Bind to container
$app->getContainer()->singleton(Session::class, fn() => $session);
$app->getContainer()->singleton(Auth::class, fn() => $auth);


// Manually register middleware instances
$app->getContainer()->instance('OOPress\Http\Middleware\AdminMiddleware', new \OOPress\Http\Middleware\AdminMiddleware());
$app->getContainer()->instance('OOPress\Http\Middleware\AuthMiddleware', new \OOPress\Http\Middleware\AuthMiddleware());
$app->getContainer()->instance('OOPress\Http\Middleware\GuestMiddleware', new \OOPress\Http\Middleware\GuestMiddleware());

// Load routes
$router = $app->getContainer()->get(Router::class);
$routes = require __DIR__ . '/../config/routes.php';
$router->addRoutes($routes);

return $app;
