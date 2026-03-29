<?php

declare(strict_types=1);

namespace OOPress\Cache\Event;

use OOPress\Event\Event;

/**
 * CacheInvalidationEvent — Dispatched when cache should be invalidated.
 * 
 * Modules can listen to this event to invalidate their own cache.
 * 
 * @api
 */
class CacheInvalidationEvent extends Event
{
    private array $tags = [];
    private array $urls = [];
    
    public function __construct(
        private readonly string $reason,
    ) {
        parent::__construct();
    }
    
    public function addTag(string $tag): self
    {
        $this->tags[] = $tag;
        return $this;
    }
    
    public function addTags(array $tags): self
    {
        $this->tags = array_merge($this->tags, $tags);
        return $this;
    }
    
    public function addUrl(string $url): self
    {
        $this->urls[] = $url;
        return $this;
    }
    
    public function getTags(): array
    {
        return $this->tags;
    }
    
    public function getUrls(): array
    {
        return $this->urls;
    }
    
    public function getReason(): string
    {
        return $this->reason;
    }
}