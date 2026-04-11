<?php

use OOPress\Controllers\HomeController;
use OOPress\Controllers\AuthController;
use OOPress\Controllers\PostController;
use OOPress\Controllers\AdminController;

$languages = 'en|es|fr|de|it|pt|ru|ja|zh|ar';

return [
    'GET' => [
        // Admin routes
        '/admin' => [AdminController::class, 'dashboard'],
        '/admin/posts' => [AdminController::class, 'posts'],
        '/admin/posts/create' => [AdminController::class, 'createPost'],
        '/admin/posts/{id}/edit' => [AdminController::class, 'editPost'],
        '/admin/posts/{id}/delete' => [AdminController::class, 'deletePost'],
        '/admin/users' => [AdminController::class, 'users'],
        '/admin/users/{id}/edit' => [AdminController::class, 'editUser'],
        
        // Auth routes
        '/login' => [AuthController::class, 'showLogin'],
        '/register' => [AuthController::class, 'showRegister'],
        '/logout' => [AuthController::class, 'logout'],
        '/dashboard' => [AuthController::class, 'dashboard'],
        
        // Public routes
        '/' => [HomeController::class, 'index'],
        '/about' => function() {
            return '<h1>' . __('About OOPress') . '</h1>';
        },
        '/post/{slug}' => [PostController::class, 'show'],
        
        // Language routes
        '/{lang:' . $languages . '}' => [HomeController::class, 'index'],
        '/{lang:' . $languages . '}/about' => function() {
            return '<h1>' . __('About OOPress') . '</h1>';
        },
        '/{lang:' . $languages . '}/post/{slug}' => [PostController::class, 'show'],
        '/{lang:' . $languages . '}/login' => [AuthController::class, 'showLogin'],
        '/{lang:' . $languages . '}/register' => [AuthController::class, 'showRegister'],
        '/{lang:' . $languages . '}/dashboard' => [AuthController::class, 'dashboard'],
    ],
    
    'POST' => [
        '/login' => [AuthController::class, 'login'],
        '/register' => [AuthController::class, 'register'],
        '/admin/posts/create' => [AdminController::class, 'createPost'],
        '/admin/posts/{id}/edit' => [AdminController::class, 'editPost'],
        '/admin/users/{id}/edit' => [AdminController::class, 'editUser'],
    ],
];