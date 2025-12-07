<?php

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../src/bootstrap.php';

use OOPress\Core\Application;

$app = new Application();
$app->runWeb();
