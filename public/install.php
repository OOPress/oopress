<?php

/**
 * OOPress Web Installer
 * 
 * This file is the entry point for the web installer.
 * It should be removed after installation.
 */

use OOPress\Kernel;
use OOPress\Installer\Controller\InstallController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

// Check if already installed
$lockFile = __DIR__ . '/../var/installed.lock';
if (file_exists($lockFile)) {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../vendor/autoload.php';

// Create minimal kernel for installer
$kernel = new Kernel(
    projectRoot: dirname(__DIR__),
    environment: 'install',
    debug: true
);
$kernel->boot();

$container = $kernel->getContainer();
$controller = $container->get(InstallController::class);

// Define installer routes
$routes = new RouteCollection();
$routes->add('install_index', new Route('/install', ['_controller' => [$controller, 'index']]));
$routes->add('install_requirements', new Route('/install/requirements', ['_controller' => [$controller, 'requirements']]));
$routes->add('install_database', new Route('/install/database', ['_controller' => [$controller, 'database']]));
$routes->add('install_site', new Route('/install/site', ['_controller' => [$controller, 'site']]));
$routes->add('install_admin', new Route('/install/admin', ['_controller' => [$controller, 'admin']]));
$routes->add('install_confirm', new Route('/install/confirm', ['_controller' => [$controller, 'confirm']]));

// Match route
$context = new RequestContext();
$context->fromRequest(Request::createFromGlobals());
$matcher = new UrlMatcher($routes, $context);

try {
    $parameters = $matcher->match($_SERVER['REQUEST_URI']);
    $controller = $parameters['_controller'];
    $response = $controller(Request::createFromGlobals());
} catch (\Exception $e) {
    $response = new Response('Page not found', 404);
}

$response->send();