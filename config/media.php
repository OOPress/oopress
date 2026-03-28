<?php

/**
 * Media configuration.
 * 
 * GDPR compliance: All media is stored locally by default.
 */

return [
    // Maximum file size (bytes)
    'max_file_size' => 20 * 1024 * 1024, // 20MB
    
    // Allowed file extensions
    'allowed_extensions' => [
        // Images
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        // Documents
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv',
        // Video
        'mp4', 'webm', 'ogg',
        // Audio
        'mp3', 'wav', 'ogg',
    ],
    
    // Image styles for different use cases
    'image_styles' => [
        'thumbnail' => [
            'width' => 150,
            'height' => 150,
            'crop' => true,
            'quality' => 85,
        ],
        'medium' => [
            'width' => 300,
            'height' => 300,
            'crop' => false,
            'quality' => 90,
        ],
        'large' => [
            'width' => 800,
            'height' => 600,
            'crop' => false,
            'quality' => 90,
        ],
        'hero' => [
            'width' => 1200,
            'height' => 400,
            'crop' => true,
            'quality' => 85,
        ],
    ],
    
    // Private files require authentication to access
    'private_files' => false,
    
    // Generate responsive image variants (srcset)
    'responsive_images' => [
        'enabled' => true,
        'breakpoints' => [320, 640, 768, 1024, 1280, 1536],
    ],
];