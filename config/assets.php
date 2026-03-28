<?php

/**
 * Asset configuration.
 * 
 * GDPR compliance: Set self_host_assets to true to avoid CDNs.
 */

return [
    // Self-host all assets (GDPR compliant, no external requests)
    'self_host_assets' => true,
    
    // Public directory for compiled assets
    'public_dir' => '/assets',
    
    // Compiler settings
    'compiler' => [
        'minify_css' => true,
        'minify_js' => true,
        'source_maps' => false,
    ],
    
    // Asset caching
    'cache' => [
        'enabled' => true,
        'ttl' => 86400, // 24 hours
    ],
    
    // Version for cache busting
    'version' => '1.0.0',
];