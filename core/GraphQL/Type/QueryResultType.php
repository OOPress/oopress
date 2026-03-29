<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * QueryResultType — GraphQL type for search results.
 * 
 * @internal
 */
class QueryResultType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'QueryResult',
            'description' => 'Search query results',
            'fields' => [
                'total' => [
                    'type' => Type::int(),
                    'description' => 'Total number of results',
                ],
                'results' => [
                    'type' => Type::listOf($types->get('Content')),
                    'description' => 'Result items',
                ],
                'facets' => [
                    'type' => Type::string(),
                    'description' => 'Facet counts (JSON)',
                ],
                'suggestions' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'Search suggestions',
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}