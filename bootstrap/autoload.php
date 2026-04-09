<?php

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use Whoops\Run;
use Whoops\Handler\PrettyPageHandler;

// Load environment
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Error handling
$whoops = new Run();
$whoops->pushHandler(new PrettyPageHandler());
$whoops->register();

// Database connection (Medoo)
use Medoo\Medoo;

$db = new Medoo([
    'type' => $_ENV['DB_TYPE'] ?? 'mysql',
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'name' => $_ENV['DB_NAME'] ?? 'oopress',
    'user' => $_ENV['DB_USER'] ?? 'root',
    'pass' => $_ENV['DB_PASS'] ?? '',
]);

// Container setup
$container = new OOPress\Core\Container();
$container->singleton(Medoo::class, fn() => $db);
$container->singleton(OOPress\Core\Database\Model::class, function($c) use ($db) {
    OOPress\Core\Database\Model::setDB($db);
    return new OOPress\Core\Database\Model();
});

return $container;
