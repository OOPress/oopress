<?php

use OOPress\Controllers\PostController;

return [
    'GET' => [
        '/' => [PostController::class, 'home'],
        '/post/{slug}' => [PostController::class, 'show'],
        '/about' => function() {
            return '<h1>About OOPress</h1><p>A lean, modern PHP CMS</p>';
        },
        '/test' => function() {
            return 'Test route is working!';
        },
    ],
    'POST' => [
        // '/contact' => [ContactController::class, 'submit'],
    ],
];