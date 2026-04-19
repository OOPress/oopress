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

// Setup database
// Setup database - Correct Medoo configuration
$db = new Medoo([
    'type' => $_ENV['DB_TYPE'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'database' => $_ENV['DB_NAME'] ?? 'oopress',
    'username' => $_ENV['DB_USER'] ?? 'root',
    'password' => $_ENV['DB_PASS'] ?? '',
    'charset' => 'utf8mb4',
    'port' => (int)($_ENV['DB_PORT'] ?? 3306),
    // Optional: for Unix sockets
    // 'socket' => $_ENV['DB_SOCKET'] ?? null,
]);

Model::setDB($db);

// Load plugin system (MOVE THIS HERE)
//use OOPress\Core\Plugin\PluginManager;

$pluginManager = new PluginManager();
$pluginManager->loadActivePluginsFromDB();

// Store in container
$app->getContainer()->singleton(PluginManager::class, fn() => $pluginManager);

// Bind database to container
$app->getContainer()->singleton(Medoo::class, fn() => $db);

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
