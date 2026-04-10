<?php

declare(strict_types=1);

use OOPress\Http\Request;

$app = require __DIR__ . '/../bootstrap/app.php';

$request = Request::fromGlobals();
$app->run($request);