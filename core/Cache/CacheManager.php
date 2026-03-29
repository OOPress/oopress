<?php

declare(strict_types=1);

namespace OOPress\Cache;

use OOPress\Event\HookDispatcher;

/**
 * CacheManager — Manages cache backends and operations.
 * 
 * @api
 */
class CacheManager implements CacheInterface
{
    private CacheInterface $backend;
    private array $config;
    private array $tags = [];
    
    public function __construct(
        private readonly HookDispatcher $hookDispatcher,
        array $config = [],
    ) {
        $this->config = array_merge([
            'default_backend' => 'file',
            'default_ttl' => 3600,
            'page_cache_enabled' => true,
            'page_cache_ttl' => 86400,
            'block_cache_enabled' => true,
            'block_cache_ttl' => 3600,
        ], $config);
        
        $this->initializeBackend();
    }
    
    /**
     * Initialize cache backend.
     */
    private function initializeBackend(): void
    {
        $backendType = $this->config['default_backend'];
        
        switch ($backendType) {
            case 'file':
                $this->backend = new Backend\FileCache(
                    $this->config['file']['path'] ?? null,
                    $this->config['file']['directory_level'] ?? 2
                );
                break;
            case 'redis':
                $this->backend = new Backend\RedisCache(
                    $this->config['redis']['host'] ?? '127.0.0.1',
                    $this->config['redis']['port'] ?? 6379,
                    $this->config['redis']['database'] ?? 0,
                    $this->config['redis']['password'] ?? null
                );
                break;
            // In CacheManager::initializeBackend()
            case 'memcached':
                if (!Backend\MemcachedCache::isAvailable()) {
                    throw new \RuntimeException('Memcached extension not loaded');
                }
                $this->backend = new Backend\MemcachedCache(
                    $this->config['memcached']['servers'] ?? [['127.0.0.1', 11211]],
                    $this->config['memcached']['prefix'] ?? 'oopress:'
                );
                break;
            case 'array':
                $this->backend = new Backend\ArrayCache();
                break;
            default:
                throw new \RuntimeException(sprintf('Unknown cache backend: %s', $backendType));
                
        }
    }
    
    /**
     * Get a cached item.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->backend->get($this->normalizeKey($key), $default);
    }
    
    /**
     * Store an item in the cache.
     */
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $ttl = $ttl ?? $this->config['default_ttl'];
        
        // Store tags if present
        if (!empty($this->tags)) {
            $value = ['_tags' => $this->tags, '_data' => $value];
            $this->storeTags($key, $this->tags);
            $this->tags = [];
        }
        
        return $this->backend->set($this->normalizeKey($key), $value, $ttl);
    }
    
    /**
     * Delete an item from the cache.
     */
    public function delete(string $key): bool
    {
        $this->deleteTags($key);
        return $this->backend->delete($this->normalizeKey($key));
    }
    
    /**
     * Delete multiple items.
     */
    public function deleteMultiple(array $keys): bool
    {
        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys);
        
        foreach ($normalizedKeys as $key) {
            $this->deleteTags($key);
        }
        
        return $this->backend->deleteMultiple($normalizedKeys);
    }
    
    /**
     * Clear the entire cache.
     */
    public function clear(): bool
    {
        $this->clearTagIndex();
        return $this->backend->clear();
    }
    
    /**
     * Check if an item exists.
     */
    public function has(string $key): bool
    {
        return $this->backend->has($this->normalizeKey($key));
    }
    
    /**
     * Get multiple items.
     */
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $normalizedKeys = array_map([$this, 'normalizeKey'], $keys);
        return $this->backend->getMultiple($normalizedKeys, $default);
    }
    
    /**
     * Store multiple items.
     */
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            $normalized[$this->normalizeKey($key)] = $value;
        }
        
        return $this->backend->setMultiple($normalized, $ttl);
    }
    
    /**
     * Add tags to the next cache operation.
     */
    public function tags(array $tags): self
    {
        $this->tags = array_merge($this->tags, $tags);
        return $this;
    }
    
    /**
     * Invalidate all items with a tag.
     */
    public function invalidateTag(string $tag): bool
    {
        return $this->invalidateTags([$tag]);
    }
    
    /**
     * Invalidate all items with tags.
     */
    public function invalidateTags(array $tags): bool
    {
        $keys = $this->getKeysByTags($tags);
        
        if (empty($keys)) {
            return true;
        }
        
        return $this->deleteMultiple($keys);
    }
    
    /**
     * Get page cache key for request.
     */
    public function getPageCacheKey(Request $request): string
    {
        $url = $request->getSchemeAndHttpHost() . $request->getPathInfo();
        $query = $request->query->all();
        
        // Sort query parameters for consistent key
        ksort($query);
        
        $key = 'page:' . $url;
        
        if (!empty($query)) {
            $key .= '?' . http_build_query($query);
        }
        
        // Add language if present
        if ($request->getSession() && $request->getSession()->has('locale')) {
            $key .= ':' . $request->getSession()->get('locale');
        }
        
        return $key;
    }
    
    /**
     * Check if response should be cached.
     */
    public function shouldCachePage(Request $request, Response $response): bool
    {
        // Only cache for anonymous users
        if ($request->getSession() && $request->getSession()->has('user_id')) {
            return false;
        }
        
        // Only cache GET requests
        if (!$request->isMethod('GET')) {
            return false;
        }
        
        // Only cache successful responses
        if ($response->getStatusCode() !== 200) {
            return false;
        }
        
        // Don't cache admin pages
        if (str_starts_with($request->getPathInfo(), '/admin')) {
            return false;
        }
        
        return $this->config['page_cache_enabled'];
    }
    
    /**
     * Normalize cache key.
     */
    private function normalizeKey(string $key): string
    {
        // Replace invalid characters
        $key = preg_replace('/[{}()\/\\\@:]/', '-', $key);
        
        // Limit length (Redis has 512MB limit, but keys should be reasonable)
        if (strlen($key) > 255) {
            $key = md5($key);
        }
        
        return $key;
    }
    
    /**
     * Store tag references for a key.
     */
    private function storeTags(string $key, array $tags): void
    {
        $tagIndexKey = 'tag_index:' . $key;
        $this->backend->set($tagIndexKey, $tags, 86400 * 30); // 30 days
        
        foreach ($tags as $tag) {
            $tagKey = 'tag:' . $tag;
            $keys = $this->backend->get($tagKey, []);
            $keys[] = $key;
            $this->backend->set($tagKey, array_unique($keys), 86400 * 30);
        }
    }
    
    /**
     * Delete tag references for a key.
     */
    private function deleteTags(string $key): void
    {
        $tagIndexKey = 'tag_index:' . $key;
        $tags = $this->backend->get($tagIndexKey, []);
        
        foreach ($tags as $tag) {
            $tagKey = 'tag:' . $tag;
            $keys = $this->backend->get($tagKey, []);
            $keys = array_diff($keys, [$key]);
            
            if (empty($keys)) {
                $this->backend->delete($tagKey);
            } else {
                $this->backend->set($tagKey, $keys, 86400 * 30);
            }
        }
        
        $this->backend->delete($tagIndexKey);
    }
    
    /**
     * Get keys by tags.
     */
    private function getKeysByTags(array $tags): array
    {
        $allKeys = [];
        
        foreach ($tags as $tag) {
            $tagKey = 'tag:' . $tag;
            $keys = $this->backend->get($tagKey, []);
            $allKeys = array_merge($allKeys, $keys);
        }
        
        return array_unique($allKeys);
    }
    
    /**
     * Clear the entire tag index.
     */
    private function clearTagIndex(): void
    {
        // This is a simplified version - in production, you'd iterate through keys
        $this->backend->deleteMultiple($this->getKeysByTags(['*']));
    }
}