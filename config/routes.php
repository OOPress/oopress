<?php

use OOPress\Controllers\PostController;

// Available language codes (from your i18n setup)
$languages = 'en|es|fr|de|it|pt|ru|ja|zh|ar';

return [
    'GET' => [
        // Static pages (no language param needed)
        '/about' => function() {
            return '<h1>' . __('About OOPress') . '</h1><p>Lean, modern PHP CMS built with clean OOP architecture.</p>';
        },
        '/test' => function() {
            return 'Test route is working!';
        },
        
        // Dynamic routes with optional language prefix
        '/' => [PostController::class, 'home'],
        '/post/{slug}' => [PostController::class, 'show'],
        
        // Language-prefixed versions (with constraint to avoid conflicts)
        '/{lang:' . $languages . '}' => [PostController::class, 'home'],
        '/{lang:' . $languages . '}/about' => function($request) {
            return '<h1>' . __('About OOPress') . '</h1><p>Language version</p>';
        },
        '/{lang:' . $languages . '}/post/{slug}' => [PostController::class, 'show'],
    ],
];