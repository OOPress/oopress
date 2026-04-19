<?php

declare(strict_types=1);

namespace OOPress\Core\Plugin;

class Hook
{
    private static array $actions = [];
    private static array $filters = [];
    
    /**
     * Add an action hook
     */
    public static function addAction(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (!isset(self::$actions[$hook])) {
            self::$actions[$hook] = [];
        }
        if (!isset(self::$actions[$hook][$priority])) {
            self::$actions[$hook][$priority] = [];
        }
        self::$actions[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs
        ];
    }
    
    /**
     * Add a filter hook
     */
    public static function addFilter(string $hook, callable $callback, int $priority = 10, int $acceptedArgs = 1): void
    {
        if (!isset(self::$filters[$hook])) {
            self::$filters[$hook] = [];
        }
        if (!isset(self::$filters[$hook][$priority])) {
            self::$filters[$hook][$priority] = [];
        }
        self::$filters[$hook][$priority][] = [
            'callback' => $callback,
            'accepted_args' => $acceptedArgs
        ];
    }
    
    /**
     * Execute an action hook
     */
    public static function doAction(string $hook, ...$args): void
    {
        if (!isset(self::$actions[$hook])) {
            return;
        }
        
        ksort(self::$actions[$hook]);
        
        foreach (self::$actions[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $numArgs = min($callback['accepted_args'], count($args));
                $callbackArgs = array_slice($args, 0, $numArgs);
                call_user_func_array($callback['callback'], $callbackArgs);
            }
        }
    }
    
    /**
     * Apply a filter hook
     */
    public static function applyFilters(string $hook, $value, ...$args): mixed
    {
        if (!isset(self::$filters[$hook])) {
            return $value;
        }
        
        ksort(self::$filters[$hook]);
        
        foreach (self::$filters[$hook] as $priority => $callbacks) {
            foreach ($callbacks as $callback) {
                $callbackArgs = array_merge([$value], $args);
                $numArgs = min($callback['accepted_args'], count($callbackArgs));
                $callbackArgs = array_slice($callbackArgs, 0, $numArgs);
                $value = call_user_func_array($callback['callback'], $callbackArgs);
            }
        }
        
        return $value;
    }
    
    /**
     * Remove an action hook
     */
    public static function removeAction(string $hook, callable $callback, int $priority = 10): bool
    {
        return self::remove(self::$actions, $hook, $callback, $priority);
    }
    
    /**
     * Remove a filter hook
     */
    public static function removeFilter(string $hook, callable $callback, int $priority = 10): bool
    {
        return self::remove(self::$filters, $hook, $callback, $priority);
    }
    
    private static function remove(array &$storage, string $hook, callable $callback, int $priority): bool
    {
        if (!isset($storage[$hook][$priority])) {
            return false;
        }
        
        foreach ($storage[$hook][$priority] as $key => $item) {
            if ($item['callback'] === $callback) {
                unset($storage[$hook][$priority][$key]);
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Check if a hook has actions
     */
    public static function hasAction(string $hook): bool
    {
        return isset(self::$actions[$hook]) && !empty(self::$actions[$hook]);
    }
    
    /**
     * Check if a hook has filters
     */
    public static function hasFilter(string $hook): bool
    {
        return isset(self::$filters[$hook]) && !empty(self::$filters[$hook]);
    }
}