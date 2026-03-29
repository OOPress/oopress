<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Resolver;

use OOPress\Block\BlockManager;
use OOPress\Block\RegionManager;
use OOPress\Security\AuthorizationManager;

/**
 * BlockResolver — Resolves block-related GraphQL fields.
 * 
 * @internal
 */
class BlockResolver
{
    public function __construct(
        private readonly BlockManager $blockManager,
        private readonly RegionManager $regionManager,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * Resolve blocks.
     */
    public function resolveBlocks($root, array $args, array $context): array
    {
        $user = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($user, 'view_blocks')) {
            return [];
        }
        
        if (isset($args['region'])) {
            return $this->blockManager->getBlocksForRegion($args['region']);
        }
        
        return $this->blockManager->getAllBlockDefinitions();
    }
    
    /**
     * Resolve regions.
     */
    public function resolveRegions($root, array $args, array $context): array
    {
        $user = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($user, 'view_regions')) {
            return [];
        }
        
        return $this->regionManager->getAllRegions();
    }
}