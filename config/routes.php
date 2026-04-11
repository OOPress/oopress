<?php

use OOPress\Controllers\HomeController;
use OOPress\Controllers\AuthController;
use OOPress\Controllers\PostController;

$languages = 'en|es|fr|de|it|pt|ru|ja|zh|ar';

return [
    'GET' => [
        // Authentication routes
        '/login' => [AuthController::class, 'showLogin'],
        '/logout' => [AuthController::class, 'logout'],
        '/dashboard' => [AuthController::class, 'dashboard'],
        
        // Public routes
        '/' => [HomeController::class, 'index'],
        '/about' => function() {
            return '<h1>' . __('About OOPress') . '</h1><p>A lean, modern PHP CMS built with clean OOP architecture.</p>';
        },
        
        // Post routes
        '/post/{slug}' => [PostController::class, 'show'],
        
        // Language-prefixed routes
        '/{lang:' . $languages . '}' => [HomeController::class, 'index'],
        '/{lang:' . $languages . '}/about' => function() {
            return '<h1>' . __('About OOPress') . '</h1>';
        },
        '/{lang:' . $languages . '}/post/{slug}' => [PostController::class, 'show'],
        '/{lang:' . $languages . '}/login' => [AuthController::class, 'showLogin'],
        '/{lang:' . $languages . '}/dashboard' => [AuthController::class, 'dashboard'],
    ],
    
    'POST' => [
        '/login' => [AuthController::class, 'login'],
    ],
];