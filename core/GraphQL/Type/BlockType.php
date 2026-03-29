<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * BlockType — GraphQL type for blocks.
 * 
 * @internal
 */
class BlockType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'Block',
            'description' => 'A renderable block',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'Block identifier',
                ],
                'label' => [
                    'type' => Type::string(),
                    'description' => 'Human-readable label',
                ],
                'description' => [
                    'type' => Type::string(),
                    'description' => 'Block description',
                ],
                'category' => [
                    'type' => Type::string(),
                    'description' => 'Block category',
                ],
                'module' => [
                    'type' => Type::string(),
                    'description' => 'Module that provides this block',
                ],
                'cacheable' => [
                    'type' => Type::boolean(),
                    'description' => 'Whether block can be cached',
                ],
                'region' => [
                    'type' => $types->get('Region'),
                    'description' => 'Assigned region',
                ],
                'weight' => [
                    'type' => Type::int(),
                    'description' => 'Display order within region',
                ],
                'status' => [
                    'type' => Type::boolean(),
                    'description' => 'Whether block is enabled',
                ],
                'settings' => [
                    'type' => Type::string(),
                    'description' => 'Block instance settings (JSON)',
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}