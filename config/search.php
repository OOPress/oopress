<?php

/**
 * Search configuration.
 * 
 * GDPR compliance: Database backend is self-hosted and GDPR compliant.
 * External backends (Elasticsearch, Algolia) are optional and require
 * explicit configuration.
 */

return [
    // Search backend: 'database', 'elasticsearch', 'algolia'
    'backend' => 'database',
    
    // Database backend settings
    'database' => [
        'table' => 'oop_search_index',
        'min_word_length' => 3,
    ],
    
    // Elasticsearch settings (optional, requires external service)
    'elasticsearch' => [
        'hosts' => ['localhost:9200'],
        'index' => 'oopress',
        'enabled' => false,
    ],
    
    // Algolia settings (optional, requires external service)
    'algolia' => [
        'app_id' => null,
        'api_key' => null,
        'index' => 'oopress',
        'enabled' => false,
    ],
    
    // Search results settings
    'results' => [
        'per_page' => 20,
        'max_per_page' => 100,
        'excerpt_length' => 200,
        'highlight_tags' => ['<mark>', '</mark>'],
    ],
    
    // Facets configuration
    'facets' => [
        'type' => 'Type',
        'content_type' => 'Content Type',
        'language' => 'Language',
        'author' => 'Author',
    ],
    
    // Suggestions (autocomplete)
    'suggestions' => [
        'enabled' => true,
        'limit' => 5,
    ],
];