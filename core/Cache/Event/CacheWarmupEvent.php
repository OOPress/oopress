<?php

declare(strict_types=1);

namespace OOPress\Cache\Event;

use OOPress\Event\Event;

/**
 * CacheWarmupEvent — Dispatched when warming up cache.
 * 
 * Modules can listen to this event to preload their own cache.
 * 
 * @api
 */
class CacheWarmupEvent extends Event
{
    private array $warmed = [];
    
    public function addWarmed(string $key): void
    {
        $this->warmed[] = $key;
    }
    
    public function getWarmed(): array
    {
        return $this->warmed;
    }
    
    public function getCount(): int
    {
        return count($this->warmed);
    }
}