<?php

declare(strict_types=1);

namespace OOPress\Core\Cache;

class CacheManager
{
    private string $cachePath;
    private int $defaultTtl;
    private bool $enabled;
    
    public function __construct()
    {
        $this->cachePath = __DIR__ . '/../../../storage/cache/';
        $this->defaultTtl = (int)\OOPress\Models\Setting::get('cache_ttl', 3600); // 1 hour default
        $this->enabled = (bool)\OOPress\Models\Setting::get('cache_enabled', true);
        
        // Create cache directory if not exists
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    /**
     * Get cache path
     */
    public function getCachePath(): string
    {
        return $this->cachePath;
    }

    /**
     * Generate cache key from string
     */
    public function key(string $key): string
    {
        return md5($key);
    }
    
    /**
     * Get cached data
     */
    public function get(string $key, $default = null)
    {
        if (!$this->enabled) {
            return $default;
        }
        
        $file = $this->cachePath . $this->key($key) . '.cache';
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = unserialize(file_get_contents($file));
        
        // Check if cache is expired
        if ($data['expires'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $data['value'];
    }
    
    /**
     * Store data in cache
     */
    public function set(string $key, $value, ?int $ttl = null): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        $ttl = $ttl ?? $this->defaultTtl;
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl,
            'created' => time()
        ];
        
        $file = $this->cachePath . $this->key($key) . '.cache';
        
        return file_put_contents($file, serialize($data)) !== false;
    }
    
    /**
     * Delete cached data
     */
    public function delete(string $key): bool
    {
        $file = $this->cachePath . $this->key($key) . '.cache';
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return false;
    }
    
    /**
     * Clear all cache
     */
    public function clear(): bool
    {
        $files = glob($this->cachePath . '*.cache');
        
        foreach ($files as $file) {
            unlink($file);
        }
        
        return true;
    }
    
    /**
     * Remember pattern - get from cache or store callback result
     */
    public function remember(string $key, callable $callback, ?int $ttl = null)
    {
        $cached = $this->get($key);
        
        if ($cached !== null) {
            return $cached;
        }
        
        $value = $callback();
        $this->set($key, $value, $ttl);
        
        return $value;
    }
    
    /**
     * Check if cache exists and is valid
     */
    public function has(string $key): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        $file = $this->cachePath . $this->key($key) . '.cache';
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($file));
        
        return $data['expires'] >= time();
    }
    
    /**
     * Increment a cached value
     */
    public function increment(string $key, int $step = 1): int
    {
        $value = $this->get($key, 0);
        $value += $step;
        $this->set($key, $value);
        
        return $value;
    }
    
    /**
     * Decrement a cached value
     */
    public function decrement(string $key, int $step = 1): int
    {
        $value = $this->get($key, 0);
        $value -= $step;
        $this->set($key, $value);
        
        return $value;
    }
}