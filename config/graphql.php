<?php

/**
 * GraphQL configuration.
 */

return [
    // Enable/disable GraphQL API
    'enabled' => true,
    
    // Enable debug mode (shows error details)
    'debug' => false,
    
    // Rate limiting (queries per minute)
    'rate_limit' => [
        'enabled' => true,
        'queries_per_minute' => 100,
    ],
    
    // Max query depth (prevents complex queries)
    'max_depth' => 10,
    
    // Max query complexity (prevents expensive queries)
    'max_complexity' => 100,
    
    // GraphQL Playground UI
    'playground' => [
        'enabled' => true,
        'path' => '/graphql/playground',
    ],
];