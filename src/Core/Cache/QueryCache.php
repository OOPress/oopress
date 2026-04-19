<?php

declare(strict_types=1);

namespace OOPress\Core\Cache;

use Medoo\Medoo;

class QueryCache
{
    private CacheManager $cache;
    private bool $enabled;
    private array $cachedQueries = [];
    
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
        $this->enabled = (bool)\OOPress\Models\Setting::get('query_cache_enabled', true);
    }
    
    /**
     * Generate cache key for a query
     */
    public function getQueryKey(string $sql, array $params = []): string
    {
        return 'query_' . md5($sql . serialize($params));
    }
    
    /**
     * Execute query with caching
     */
    public function query(Medoo $db, string $sql, array $params = [], ?int $ttl = null)
    {
        if (!$this->enabled) {
            return $db->query($sql, $params)->fetchAll();
        }
        
        $key = $this->getQueryKey($sql, $params);
        
        return $this->cache->remember($key, function() use ($db, $sql, $params) {
            return $db->query($sql, $params)->fetchAll();
        }, $ttl);
    }
    
    /**
     * Clear query cache
     */
    public function clear(): void
    {
        $files = glob($this->cache->getCachePath() . 'query_*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
    
    /**
     * Invalidate cache for a specific table
     */
    public function invalidateTable(string $table): void
    {
        // Simple approach: clear all query cache
        // For production, you'd want more granular invalidation
        $this->clear();
    }
}