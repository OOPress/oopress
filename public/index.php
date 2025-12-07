<?php
declare(strict_types=1);

// Autoload dependencies via Composer
require_once __DIR__ . '/../vendor/autoload.php';

use OOPress\Core\Application;

// Instantiate the Application
$app = new Application();

// Run the web version
$app->runWeb();
