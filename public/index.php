<?php

require_once __DIR__ . '/../vendor/autoload.php';

use OOPress\Kernel;
use Symfony\Component\HttpFoundation\Request;

$kernel = new Kernel(
    projectRoot: dirname(__DIR__),
    environment: 'dev',
    debug: true
);

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();