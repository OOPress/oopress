<?php

declare(strict_types=1);

namespace OOPress\Cache;

/**
 * CacheInterface — Contract for cache backends.
 * 
 * @api
 */
interface CacheInterface
{
    /**
     * Get a cached item.
     */
    public function get(string $key, mixed $default = null): mixed;
    
    /**
     * Store an item in the cache.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool;
    
    /**
     * Delete an item from the cache.
     */
    public function delete(string $key): bool;
    
    /**
     * Delete multiple items.
     */
    public function deleteMultiple(array $keys): bool;
    
    /**
     * Clear the entire cache.
     */
    public function clear(): bool;
    
    /**
     * Check if an item exists.
     */
    public function has(string $key): bool;
    
    /**
     * Get multiple items.
     */
    public function getMultiple(array $keys, mixed $default = null): array;
    
    /**
     * Store multiple items.
     */
    public function setMultiple(array $values, ?int $ttl = null): bool;
    
    /**
     * Delete items by tag.
     */
    public function invalidateTag(string $tag): bool;
    
    /**
     * Delete items by multiple tags.
     */
    public function invalidateTags(array $tags): bool;
}