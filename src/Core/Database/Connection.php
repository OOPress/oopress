<?php

declare(strict_types=1);

namespace OOPress\Core\Database;

use Medoo\Medoo;

class Connection
{
    private static ?Medoo $instance = null;
    
    public static function getInstance(array $config): Medoo
    {
        if (self::$instance === null) {
            self::$instance = new Medoo([
                'type' => $config['driver'] ?? 'mysql',
                'host' => $config['host'] ?? 'localhost',
                'database' => $config['database'] ?? 'oopress',
                'username' => $config['username'] ?? 'root',
                'password' => $config['password'] ?? '',
                'charset' => $config['charset'] ?? 'utf8mb4',
                'port' => $config['port'] ?? 3306,
            ]);
        }
        
        return self::$instance;
    }
}