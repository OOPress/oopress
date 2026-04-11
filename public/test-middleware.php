<?php

require __DIR__ . '/../vendor/autoload.php';

use OOPress\Http\Middleware\AdminMiddleware;

echo "<h1>Middleware Test</h1>";

if (class_exists('OOPress\Http\Middleware\AdminMiddleware')) {
    echo "✓ AdminMiddleware class exists\n";
    
    $middleware = new AdminMiddleware();
    echo "✓ AdminMiddleware instantiated successfully\n";
    echo "Class: " . get_class($middleware) . "\n";
} else {
    echo "✗ AdminMiddleware class NOT found\n";
    echo "Check that the file exists at: src/Http/Middleware/AdminMiddleware.php\n";
}