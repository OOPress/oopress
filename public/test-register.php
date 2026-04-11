<?php

require __DIR__ . '/../bootstrap/app.php';

use OOPress\Models\User;

// Check the test user you registered
$user = User::firstWhere(['username' => 'testuser']);

if ($user) {
    echo "<h1>User Found</h1>";
    echo "<pre>";
    echo "Username: " . $user->username . "\n";
    echo "Email: " . $user->email . "\n";
    echo "Password hash: " . $user->password . "\n";
    echo "Password hash length: " . strlen($user->password) . "\n";
    echo "Status: " . $user->status . "\n";
    echo "Role: " . $user->role . "\n";
    echo "</pre>";
    
    // Test password verification
    echo "<h2>Password Test</h2>";
    $testPassword = 'test123'; // The password you used
    echo "Testing password: '{$testPassword}'\n";
    echo "Password verify result: " . (password_verify($testPassword, $user->password) ? '✓ MATCHES' : '✗ DOES NOT MATCH') . "\n";
    
    // If it doesn't match, let's check what was actually hashed
    echo "\n<h2>Hash Info:</h2>\n";
    $info = password_get_info($user->password);
    echo "<pre>";
    print_r($info);
    echo "</pre>";
} else {
    echo "User 'testuser' not found\n";
}