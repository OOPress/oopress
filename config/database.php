<?php

use Medoo\Medoo;

// Load environment if not already loaded and .env exists
if (!isset($_ENV['DB_TYPE']) && file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->safeLoad();
}

// Only create database connection if we have the required environment variables
if (isset($_ENV['DB_HOST']) && isset($_ENV['DB_NAME'])) {
    return new Medoo([
        'type' => $_ENV['DB_TYPE'] ?? 'mysql',
        'host' => $_ENV['DB_HOST'],
        'database' => $_ENV['DB_NAME'],
        'username' => $_ENV['DB_USER'] ?? 'root',
        'password' => $_ENV['DB_PASS'] ?? '',
        'charset' => 'utf8mb4',
        'port' => (int)($_ENV['DB_PORT'] ?? 3306),
        'logging' => $_ENV['APP_DEBUG'] ?? false
    ]);
}

// Return null if no database configuration is available (during installation)
return null;