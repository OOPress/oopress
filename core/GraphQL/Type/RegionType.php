<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * RegionType — GraphQL type for theme regions.
 * 
 * @internal
 */
class RegionType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'Region',
            'description' => 'A theme region where blocks can be placed',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'Region identifier',
                ],
                'label' => [
                    'type' => Type::string(),
                    'description' => 'Human-readable label',
                ],
                'description' => [
                    'type' => Type::string(),
                    'description' => 'Region description',
                ],
                'is_admin_region' => [
                    'type' => Type::boolean(),
                    'description' => 'Whether this is an admin-only region',
                ],
                'blocks' => [
                    'type' => Type::listOf($types->get('Block')),
                    'description' => 'Blocks assigned to this region',
                    'resolve' => function($region, $args, $context) {
                        $blockResolver = new \OOPress\GraphQL\Resolver\BlockResolver(
                            $context['container']->get(\OOPress\Block\BlockManager::class),
                            $context['container']->get(\OOPress\Block\RegionManager::class),
                            $context['container']->get(\OOPress\Security\AuthorizationManager::class)
                        );
                        return $blockResolver->resolveBlocks(null, ['region' => $region->id], $context);
                    },
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}