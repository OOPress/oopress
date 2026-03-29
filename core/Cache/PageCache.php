<?php

declare(strict_types=1);

namespace OOPress\Cache;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * PageCache — Handles page-level caching.
 * 
 * @api
 */
class PageCache
{
    public function __construct(
        private readonly CacheManager $cache,
        private readonly array $config = [],
    ) {}
    
    /**
     * Get cached page response.
     */
    public function get(Request $request): ?Response
    {
        if (!$this->isEnabled()) {
            return null;
        }
        
        $key = $this->cache->getPageCacheKey($request);
        $cached = $this->cache->get($key);
        
        if (!$cached) {
            return null;
        }
        
        $response = new Response(
            $cached['content'],
            $cached['status_code'],
            $cached['headers']
        );
        
        // Add cache header
        $response->headers->set('X-OOPress-Cache', 'HIT');
        
        return $response;
    }
    
    /**
     * Store page response in cache.
     */
    public function set(Request $request, Response $response): void
    {
        if (!$this->shouldCache($request, $response)) {
            return;
        }
        
        $key = $this->cache->getPageCacheKey($request);
        
        $cached = [
            'content' => $response->getContent(),
            'status_code' => $response->getStatusCode(),
            'headers' => $this->getCacheableHeaders($response),
        ];
        
        $tags = $this->getPageTags($request);
        
        $this->cache->tags($tags)->set($key, $cached, $this->config['ttl'] ?? 86400);
        
        $response->headers->set('X-OOPress-Cache', 'MISS');
    }
    
    /**
     * Invalidate page cache for a URL or tag.
     */
    public function invalidate(string $url = null, array $tags = []): void
    {
        if ($url) {
            $key = 'page:' . $url;
            $this->cache->delete($key);
        }
        
        if (!empty($tags)) {
            $this->cache->invalidateTags($tags);
        }
    }
    
    /**
     * Clear all page cache.
     */
    public function clear(): void
    {
        $this->cache->invalidateTag('page');
    }
    
    /**
     * Check if page cache is enabled.
     */
    private function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }
    
    /**
     * Check if response should be cached.
     */
    private function shouldCache(Request $request, Response $response): bool
    {
        return $this->cache->shouldCachePage($request, $response);
    }
    
    /**
     * Get cacheable headers (strip session cookies, etc.).
     */
    private function getCacheableHeaders(Response $response): array
    {
        $headers = $response->headers->all();
        
        // Remove session cookies
        unset($headers['set-cookie']);
        
        return $headers;
    }
    
    /**
     * Get tags for a page.
     */
    private function getPageTags(Request $request): array
    {
        $tags = ['page'];
        
        // Add path tag
        $path = trim($request->getPathInfo(), '/');
        if ($path) {
            $tags[] = 'page:' . str_replace('/', '-', $path);
        }
        
        return $tags;
    }
}