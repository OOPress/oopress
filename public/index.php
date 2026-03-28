<?php

// public/index.php

use OOPress\Kernel;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . '/../vendor/autoload.php';

// Determine environment
$environment = getenv('APP_ENV') ?: 'prod';
$debug = $environment === 'dev';

// Create and boot kernel
$kernel = new Kernel(
    projectRoot: dirname(__DIR__),
    environment: $environment,
    debug: $debug
);

// Handle request
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();

// Shutdown
$kernel->shutdown();
