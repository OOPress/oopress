<?php

declare(strict_types=1);

use OOPress\Core\Application;
use OOPress\Core\Router;
use OOPress\Core\Database\Model;
use Medoo\Medoo;

// Load Composer autoloader
require __DIR__ . '/../vendor/autoload.php';

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

// Bind database to container
$app->getContainer()->singleton(Medoo::class, fn() => $db);

// Load routes - New array-based approach
$router = $app->getContainer()->get(Router::class);
$routes = require __DIR__ . '/../config/routes.php';
$router->addRoutes($routes);

return $app;