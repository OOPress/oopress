<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use OOPress\GraphQL\Resolver\ContentResolver;
use OOPress\GraphQL\Resolver\UserResolver;
use OOPress\GraphQL\Resolver\BlockResolver;
use OOPress\GraphQL\Resolver\MediaResolver;
use OOPress\Event\HookDispatcher;

/**
 * QueryType — Root Query type for GraphQL.
 * 
 * @internal
 */
class QueryType extends ObjectType
{
    public function __construct(
        private readonly Types $types,
        private readonly HookDispatcher $hookDispatcher,
    ) {
        $config = [
            'name' => 'Query',
            'fields' => [
                // Content queries
                'content' => [
                    'type' => $this->types->get('Content'),
                    'args' => [
                        'id' => ['type' => Type::int()],
                        'slug' => ['type' => Type::string()],
                    ],
                    'resolve' => [new ContentResolver(), 'resolveContent'],
                ],
                'contents' => [
                    'type' => Type::listOf($this->types->get('Content')),
                    'args' => [
                        'type' => ['type' => Type::string()],
                        'status' => ['type' => $this->types->get('ContentStatus')],
                        'language' => ['type' => Type::string()],
                        'limit' => ['type' => Type::int(), 'defaultValue' => 20],
                        'offset' => ['type' => Type::int(), 'defaultValue' => 0],
                        'sortBy' => ['type' => Type::string(), 'defaultValue' => 'created_at'],
                        'sortOrder' => ['type' => $this->types->get('SortOrder'), 'defaultValue' => 'DESC'],
                    ],
                    'resolve' => [new ContentResolver(), 'resolveContents'],
                ],
                
                // User queries
                'user' => [
                    'type' => $this->types->get('User'),
                    'args' => [
                        'id' => ['type' => Type::int()],
                        'username' => ['type' => Type::string()],
                    ],
                    'resolve' => [new UserResolver(), 'resolveUser'],
                ],
                'users' => [
                    'type' => Type::listOf($this->types->get('User')),
                    'args' => [
                        'limit' => ['type' => Type::int(), 'defaultValue' => 20],
                        'offset' => ['type' => Type::int(), 'defaultValue' => 0],
                    ],
                    'resolve' => [new UserResolver(), 'resolveUsers'],
                ],
                'me' => [
                    'type' => $this->types->get('User'),
                    'resolve' => [new UserResolver(), 'resolveMe'],
                ],
                
                // Block queries
                'blocks' => [
                    'type' => Type::listOf($this->types->get('Block')),
                    'args' => [
                        'region' => ['type' => Type::string()],
                    ],
                    'resolve' => [new BlockResolver(), 'resolveBlocks'],
                ],
                'regions' => [
                    'type' => Type::listOf($this->types->get('Region')),
                    'resolve' => [new BlockResolver(), 'resolveRegions'],
                ],
                
                // Media queries
                'media' => [
                    'type' => $this->types->get('Media'),
                    'args' => [
                        'id' => ['type' => Type::int()],
                    ],
                    'resolve' => [new MediaResolver(), 'resolveMedia'],
                ],
                'mediaList' => [
                    'type' => Type::listOf($this->types->get('Media')),
                    'args' => [
                        'type' => ['type' => Type::string()],
                        'limit' => ['type' => Type::int(), 'defaultValue' => 20],
                        'offset' => ['type' => Type::int(), 'defaultValue' => 0],
                    ],
                    'resolve' => [new MediaResolver(), 'resolveMediaList'],
                ],
                
                // Search query
                'search' => [
                    'type' => $this->types->get('QueryResult'),
                    'args' => [
                        'query' => ['type' => Type::nonNull(Type::string())],
                        'limit' => ['type' => Type::int(), 'defaultValue' => 20],
                        'offset' => ['type' => Type::int(), 'defaultValue' => 0],
                    ],
                    'resolve' => [new ContentResolver(), 'resolveSearch'],
                ],
            ],
        ];
        
        // Dispatch event to add custom query fields
        $event = new Event\QueryTypeBuildEvent($config, $this->types);
        $this->hookDispatcher->dispatch($event, 'graphql.query.fields');
        
        parent::__construct($config);
    }
}