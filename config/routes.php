<?php

use OOPress\Controllers\HomeController;
use OOPress\Controllers\AuthController;
use OOPress\Controllers\PostController;
use OOPress\Controllers\AdminController;
use OOPress\Controllers\MediaController;
use OOPress\Controllers\TaxonomyController;
use OOPress\Controllers\SettingsController;
use OOPress\Controllers\CommentController;
use OOPress\Controllers\SitemapController;
use OOPress\Controllers\PluginController;
use OOPress\Controllers\CacheController;

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
        '/admin/media' => [MediaController::class, 'index'],
        '/admin/media/{id}/delete' => [MediaController::class, 'delete'],
        '/admin/categories' => [TaxonomyController::class, 'categories'],
        '/admin/categories/{id}/edit' => [TaxonomyController::class, 'editCategory'],
        '/admin/categories/{id}/delete' => [TaxonomyController::class, 'deleteCategory'],
        '/admin/tags' => [TaxonomyController::class, 'tags'],
        '/admin/tags/{id}/edit' => [TaxonomyController::class, 'editTag'],
        '/admin/tags/{id}/delete' => [TaxonomyController::class, 'deleteTag'],
        '/admin/settings' => [SettingsController::class, 'index'],
        '/admin/comments' => [CommentController::class, 'index'],
        '/admin/comments/{id}/approve' => [CommentController::class, 'approve'],
        '/admin/comments/{id}/spam' => [CommentController::class, 'spam'],
        '/admin/comments/{id}/trash' => [CommentController::class, 'trash'],
        '/admin/comments/{id}/delete' => [CommentController::class, 'delete'],
        '/admin/themes' => [ThemeController::class, 'index'],
        '/admin/themes/activate' => [ThemeController::class, 'activate'],
        '/admin/plugins' => [PluginController::class, 'index'],
        '/admin/cache' => [CacheController::class, 'index'],

        '/sitemap.xml' => [SitemapController::class, 'index'],

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
        '/post/{slug}' => [HomeController::class, 'show'],
        '/category/{slug}' => [HomeController::class, 'category'],
        '/tag/{slug}' => [HomeController::class, 'tag'],
        
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
        '/comment/submit' => [CommentController::class, 'submit'],
        '/admin/posts/create' => [AdminController::class, 'createPost'],
        '/admin/posts/{id}/edit' => [AdminController::class, 'editPost'],
        '/admin/users/{id}/edit' => [AdminController::class, 'editUser'],
        '/admin/media/upload' => [MediaController::class, 'upload'],
        '/admin/media/ajax-upload' => [MediaController::class, 'ajaxUpload'],
        '/admin/categories/create' => [TaxonomyController::class, 'createCategory'],
        '/admin/categories/{id}/edit' => [TaxonomyController::class, 'editCategory'],
        '/admin/tags/create' => [TaxonomyController::class, 'createTag'],
        '/admin/tags/{id}/edit' => [TaxonomyController::class, 'editTag'],
        '/admin/settings/save' => [SettingsController::class, 'save'],
        '/admin/plugins/activate' => [PluginController::class, 'activate'],
        '/admin/plugins/deactivate' => [PluginController::class, 'deactivate'],
        '/admin/cache/clear' => [CacheController::class, 'clear'],
    ],
];