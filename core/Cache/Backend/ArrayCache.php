<?php

declare(strict_types=1);

namespace OOPress\Cache\Backend;

use OOPress\Cache\CacheInterface;

/**
 * ArrayCache — In-memory array cache (useful for testing).
 * 
 * @internal
 */
class ArrayCache implements CacheInterface
{
    private array $cache = [];
    private array $expires = [];
    
    public function get(string $key, mixed $default = null): mixed
    {
        $this->gc($key);
        
        return $this->cache[$key] ?? $default;
    }
    
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $this->cache[$key] = $value;
        
        if ($ttl !== null) {
            $this->expires[$key] = time() + $ttl;
        }
        
        return true;
    }
    
    public function delete(string $key): bool
    {
        unset($this->cache[$key], $this->expires[$key]);
        return true;
    }
    
    public function deleteMultiple(array $keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
        
        return true;
    }
    
    public function clear(): bool
    {
        $this->cache = [];
        $this->expires = [];
        return true;
    }
    
    public function has(string $key): bool
    {
        $this->gc($key);
        return isset($this->cache[$key]);
    }
    
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        
        return $results;
    }
    
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value, $ttl);
        }
        
        return true;
    }
    
    public function invalidateTag(string $tag): bool
    {
        return true;
    }
    
    public function invalidateTags(array $tags): bool
    {
        return true;
    }
    
    private function gc(string $key): void
    {
        if (isset($this->expires[$key]) && $this->expires[$key] < time()) {
            $this->delete($key);
        }
    }
}