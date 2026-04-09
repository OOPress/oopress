<?php

$container = require __DIR__ . '/../bootstrap/autoload.php';

use OOPress\Http\Request;
use OOPress\Core\Router;

$request = Request::fromGlobals();
$router = $container->make(Router::class);

$response = $router->dispatch($request);
$response->send();
