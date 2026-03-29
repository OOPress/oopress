<?php

/**
 * Logging configuration.
 * 
 * GDPR compliant: All logs stored locally. No external logging services.
 */

return [
    // Minimum log level: debug, info, notice, warning, error, critical, alert, emergency
    'min_level' => 'info',
    
    // Log channels
    'channels' => ['default', 'security', 'api', 'database', 'cache'],
    
    // Log handlers: file, database, syslog, null
    'handlers' => ['file', 'database'],
    
    // File handler configuration
    'file' => [
        'path' => __DIR__ . '/../var/logs/oopress.log',
        'max_files' => 30,        // Number of rotated log files to keep
        'max_size' => 10 * 1024 * 1024, // 10MB
        'level' => 'info',
    ],
    
    // Database handler configuration
    'database' => [
        'table' => 'oop_logs',
        'level' => 'warning', // Only store warnings and above in database
    ],
    
    // Syslog handler configuration (optional)
    'syslog' => [
        'ident' => 'oopress',
        'facility' => LOG_USER,
        'level' => 'error',
    ],
    
    // Performance monitoring
    'performance' => [
        'enabled' => true,
        'slow_threshold' => 1.0, // Log operations taking more than 1 second
        'sample_rate' => 0.1,    // Sample 10% of requests for detailed metrics
    ],
    
    // Error tracking
    'error_tracking' => [
        'enabled' => true,
        'aggregate' => true,     // Group identical errors
    ],
    
    // Log retention
    'retention' => [
        'database_days' => 90,   // Keep database logs for 90 days
        'file_days' => 30,       // Keep file logs for 30 days
    ],
];