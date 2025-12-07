<?php
namespace OOPress\Core;

class Config
{
    private static array $config = [];

    public static function load(string $path): void
    {
        foreach (glob($path . '/*.php') as $file) {
            $key = basename($file, '.php');
            self::$config[$key] = require $file;
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $segments = explode('.', $key);
        $value = self::$config;

        foreach ($segments as $segment) {
            if (!array_key_exists($segment, $value)) {
                return $default;
            }
            $value = $value[$segment];
        }

        return $value;
    }
}
