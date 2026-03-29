<?php

/**
 * Cache configuration.
 * 
 * GDPR compliance: All cache backends are self-hosted.
 */

return [
    // Default cache backend: 'file', 'redis', 'memcached', 'array'
    'default_backend' => 'file',
    
    // Default TTL in seconds
    'default_ttl' => 3600,
    
    // File cache settings
    'file' => [
        'path' => __DIR__ . '/../var/cache',
        'directory_level' => 2,
    ],
    
    // Redis cache settings (optional)
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'database' => 0,
        'password' => null,
    ],
    
    // Memcached settings (optional)
    'memcached' => [
        'servers' => [
            ['127.0.0.1', 11211],
        ],
        // Additional options
        'options' => [
            // \Memcached::OPT_COMPRESSION => true,
            // \Memcached::OPT_TCP_NODELAY => true,
        ],
    ],
    
    // Page cache settings
    'page_cache' => [
        'enabled' => true,
        'ttl' => 86400, // 24 hours
        'exclude_paths' => [
            '/admin',
            '/login',
            '/register',
        ],
    ],
    
    // Block cache settings
    'block_cache' => [
        'enabled' => true,
        'default_ttl' => 3600,
    ],
    
    // Cache contexts (for variation)
    'contexts' => [
        'user.roles',
        'user',
        'language',
        'url.path',
        'url.query',
    ],
];