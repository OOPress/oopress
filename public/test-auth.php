<?php

require __DIR__ . '/../bootstrap/app.php';

use OOPress\Core\Session;
use OOPress\Models\User;

$session = new Session();

echo "<h1>Auth Test</h1>";

// Test session
$session->set('test', 'working');
echo "<p>Session test: " . $session->get('test') . "</p>";

// Test user model
$user = User::find(1);
if ($user) {
    echo "<p>User found: " . $user->username . "</p>";
    echo "<p>Password verify test: " . ($user->verifyPassword('admin123') ? '✓ works' : '✗ fails') . "</p>";
} else {
    echo "<p>No user found with ID 1</p>";
}