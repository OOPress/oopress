<?php

return [
    'name' => $_ENV['APP_NAME'] ?? 'OOPress',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => (bool) ($_ENV['APP_DEBUG'] ?? false),
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'UTC',
];