# Configuration Guide

## Core Configuration (config/core.php)

```php
return [
    'site' => [
        'name' => 'My OOPress Site',
        'url' => 'https://example.com',
        'timezone' => 'UTC',
        'language' => 'en',
    ],
    'debug' => false,
    'environment' => 'prod',
];

```

## Database Configuration (config/database.php)

```php
return [
    'driver' => 'pdo_mysql',
    'host' => 'localhost',
    'port' => 3306,
    'dbname' => 'oopress',
    'user' => 'username',
    'password' => 'password',
    'charset' => 'utf8mb4',
    'table_prefix' => 'oop_',
];

```

## Cache Configuration (config/cache.php)

```php
return [
    'default_backend' => 'file',
    'default_ttl' => 3600,
    'page_cache' => [
        'enabled' => true,
        'ttl' => 86400,
    ],
];

```

## Media Configuration (config/media.php)

```php
return [
    'max_file_size' => 20 * 1024 * 1024,
    'allowed_extensions' => ['jpg', 'png', 'gif', 'pdf', 'doc'],
    'image_styles' => [
        'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
        'medium' => ['width' => 300, 'height' => 300],
        'large' => ['width' => 800, 'height' => 600],
    ],
];

```

## Search Configuration (config/search.php)

```php
return [
    'backend' => 'database',
    'results_per_page' => 20,
    'min_word_length' => 3,
];

```

## Security Configuration (config/security.php)

```php
return [
    'password_policy' => [
        'min_length' => 8,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_number' => true,
        'require_symbol' => false,
    ],
    'session' => [
        'lifetime' => 7200,
        'secure' => true,
        'httponly' => true,
    ],
];

```

## Environment Variables

```text
APP_ENV=production
APP_DEBUG=false
DB_HOST=localhost
DB_NAME=oopress
DB_USER=username
DB_PASSWORD=secret
```