<?php

declare(strict_types=1);

namespace OOPress\Cache;

use OOPress\Block\BlockInterface;
use OOPress\Block\BlockAssignment;
use Symfony\Component\HttpFoundation\Request;

/**
 * BlockCache — Handles block-level caching.
 * 
 * @api
 */
class BlockCache
{
    public function __construct(
        private readonly CacheManager $cache,
        private readonly array $config = [],
    ) {}
    
    /**
     * Get cached block content.
     */
    public function get(BlockInterface $block, BlockAssignment $assignment, Request $request): ?string
    {
        if (!$block->isCacheable() || !$this->isEnabled()) {
            return null;
        }
        
        $key = $this->getCacheKey($block, $assignment, $request);
        
        return $this->cache->get($key);
    }
    
    /**
     * Store block content in cache.
     */
    public function set(BlockInterface $block, BlockAssignment $assignment, Request $request, string $content): void
    {
        if (!$block->isCacheable() || !$this->isEnabled()) {
            return;
        }
        
        $key = $this->getCacheKey($block, $assignment, $request);
        $tags = array_merge($block->getCacheTags(), ['block', 'block:' . $block->getId()]);
        $contexts = $block->getCacheContexts();
        
        // Add request context to cache key
        $key .= $this->getContextKey($request, $contexts);
        
        $ttl = $assignment->getSetting('cache_ttl', $this->config['default_ttl'] ?? 3600);
        
        $this->cache->tags($tags)->set($key, $content, $ttl);
    }
    
    /**
     * Invalidate block cache by tag.
     */
    public function invalidate(array $tags): void
    {
        $this->cache->invalidateTags($tags);
    }
    
    /**
     * Clear all block cache.
     */
    public function clear(): void
    {
        $this->cache->invalidateTag('block');
    }
    
    /**
     * Check if block cache is enabled.
     */
    private function isEnabled(): bool
    {
        return $this->config['enabled'] ?? true;
    }
    
    /**
     * Get cache key for block.
     */
    private function getCacheKey(BlockInterface $block, BlockAssignment $assignment, Request $request): string
    {
        $key = 'block:' . $block->getId();
        
        // Add assignment settings
        $key .= ':' . md5(json_encode($assignment->settings));
        
        // Add current URL path
        $key .= ':' . $request->getPathInfo();
        
        return $key;
    }
    
    /**
     * Add context to cache key.
     */
    private function getContextKey(Request $request, array $contexts): string
    {
        $parts = [];
        
        foreach ($contexts as $context) {
            switch ($context) {
                case 'user.roles':
                    $session = $request->getSession();
                    $roles = $session ? $session->get('user_roles', ['anonymous']) : ['anonymous'];
                    $parts[] = 'roles:' . implode(',', $roles);
                    break;
                    
                case 'user':
                    $session = $request->getSession();
                    $userId = $session ? $session->get('user_id', 0) : 0;
                    $parts[] = 'user:' . $userId;
                    break;
                    
                case 'language':
                    $session = $request->getSession();
                    $locale = $session ? $session->get('locale', 'en') : 'en';
                    $parts[] = 'lang:' . $locale;
                    break;
                    
                case 'url.path':
                    $parts[] = 'path:' . $request->getPathInfo();
                    break;
                    
                case 'url.query':
                    $parts[] = 'query:' . md5(http_build_query($request->query->all()));
                    break;
            }
        }
        
        return empty($parts) ? '' : ':' . implode(':', $parts);
    }
}