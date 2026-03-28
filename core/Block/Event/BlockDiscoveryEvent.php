<?php

declare(strict_types=1);

namespace OOPress\Block\Event;

use OOPress\Block\BlockManager;
use OOPress\Event\Event;

/**
 * BlockDiscoveryEvent — Dispatched when blocks should be registered.
 * 
 * Modules can listen to this event to register blocks programmatically.
 * 
 * @api
 */
class BlockDiscoveryEvent extends Event
{
    public function __construct(
        private readonly BlockManager $blockManager,
    ) {
        parent::__construct();
    }
    
    public function getBlockManager(): BlockManager
    {
        return $this->blockManager;
    }
}
