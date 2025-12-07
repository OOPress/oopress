<?php
declare(strict_types=1);

use OOPress\Core\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// Autoload Composer dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
    $dotenv->load();
}

// Create HTTP Request from globals
$request = Request::createFromGlobals();

// Initialize the application core
$app = new Application();
$response = $app->handle($request);

// Send the response back to the client
$response->send();
