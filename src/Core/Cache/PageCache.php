<?php

declare(strict_types=1);

namespace OOPress\Core\Cache;

use OOPress\Http\Request;
use OOPress\Http\Response;

class PageCache
{
    private CacheManager $cache;
    private bool $enabled;
    private array $excludedPaths = [];
    
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
        $this->enabled = (bool)\OOPress\Models\Setting::get('page_cache_enabled', false);
        $this->excludedPaths = explode("\n", \OOPress\Models\Setting::get('page_cache_excluded', "/admin\n/login\n/register\n/dashboard\n/logout"));
    }
    
    /**
     * Get cache key for current request
     */
    private function getCacheKey(Request $request): string
    {
        $url = $request->path();
        $query = http_build_query($request->all());
        $method = $request->method();
        
        return 'page_' . $method . '_' . $url . ($query ? '_' . md5($query) : '');
    }
    
    /**
     * Check if path should be cached
     */
    private function shouldCache(Request $request): bool
    {
        if (!$this->enabled) {
            return false;
        }
        
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return false;
        }
        
        // Check excluded paths
        $path = $request->path();
        foreach ($this->excludedPaths as $excluded) {
            $excluded = trim($excluded);
            if (!empty($excluded) && strpos($path, $excluded) === 0) {
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Get cached page response
     */
    public function get(Request $request): ?Response
    {
        if (!$this->shouldCache($request)) {
            return null;
        }
        
        $key = $this->getCacheKey($request);
        $cached = $this->cache->get($key);
        
        if ($cached) {
            $response = new Response($cached['content'], $cached['statusCode']);
            $response->header('X-OOPress-Cache', 'HIT');
            return $response;
        }
        
        return null;
    }
    
    /**
     * Store page response in cache
     */
    public function set(Request $request, Response $response): void
    {
        if (!$this->shouldCache($request)) {
            return;
        }
        
        // Don't cache error responses
        if ($response->getStatusCode() >= 400) {
            return;
        }
        
        $key = $this->getCacheKey($request);
        
        $this->cache->set($key, [
            'content' => $response->getContent(),
            'statusCode' => $response->getStatusCode()
        ]);
    }
    
    /**
     * Clear page cache
     */
    public function clear(): void
    {
        $files = glob($this->cache->getCachePath() . 'page_*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}