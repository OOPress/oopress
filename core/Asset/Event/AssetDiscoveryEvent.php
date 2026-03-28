<?php

declare(strict_types=1);

namespace OOPress\Asset\Event;

use OOPress\Asset\AssetManager;
use OOPress\Event\Event;

/**
 * AssetDiscoveryEvent — Dispatched when assets should be registered.
 * 
 * Modules can listen to this event to register assets programmatically.
 * 
 * @api
 */
class AssetDiscoveryEvent extends Event
{
    public function __construct(
        private readonly AssetManager $assetManager,
    ) {
        parent::__construct();
    }
    
    public function getAssetManager(): AssetManager
    {
        return $this->assetManager;
    }
}