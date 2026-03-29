<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;
use OOPress\GraphQL\Resolver\ContentResolver;
use OOPress\GraphQL\Resolver\UserResolver;
use OOPress\GraphQL\Resolver\MediaResolver;
use OOPress\Event\HookDispatcher;

/**
 * MutationType — Root Mutation type for GraphQL.
 * 
 * @internal
 */
class MutationType extends ObjectType
{
    public function __construct(
        private readonly Types $types,
        private readonly HookDispatcher $hookDispatcher,
    ) {
        $config = [
            'name' => 'Mutation',
            'fields' => [
                // Content mutations
                'createContent' => [
                    'type' => $this->types->get('Content'),
                    'args' => [
                        'input' => ['type' => Type::nonNull($this->types->get('ContentInput'))],
                    ],
                    'resolve' => [new ContentResolver(), 'resolveCreateContent'],
                ],
                'updateContent' => [
                    'type' => $this->types->get('Content'),
                    'args' => [
                        'id' => ['type' => Type::nonNull(Type::int())],
                        'input' => ['type' => Type::nonNull($this->types->get('ContentInput'))],
                    ],
                    'resolve' => [new ContentResolver(), 'resolveUpdateContent'],
                ],
                'deleteContent' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'id' => ['type' => Type::nonNull(Type::int())],
                    ],
                    'resolve' => [new ContentResolver(), 'resolveDeleteContent'],
                ],
                'publishContent' => [
                    'type' => $this->types->get('Content'),
                    'args' => [
                        'id' => ['type' => Type::nonNull(Type::int())],
                    ],
                    'resolve' => [new ContentResolver(), 'resolvePublishContent'],
                ],
                
                // User mutations
                'createUser' => [
                    'type' => $this->types->get('User'),
                    'args' => [
                        'input' => ['type' => Type::nonNull($this->types->get('UserInput'))],
                    ],
                    'resolve' => [new UserResolver(), 'resolveCreateUser'],
                ],
                'updateUser' => [
                    'type' => $this->types->get('User'),
                    'args' => [
                        'id' => ['type' => Type::nonNull(Type::int())],
                        'input' => ['type' => Type::nonNull($this->types->get('UserInput'))],
                    ],
                    'resolve' => [new UserResolver(), 'resolveUpdateUser'],
                ],
                'deleteUser' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'id' => ['type' => Type::nonNull(Type::int())],
                    ],
                    'resolve' => [new UserResolver(), 'resolveDeleteUser'],
                ],
                
                // Media mutations
                'uploadMedia' => [
                    'type' => $this->types->get('Media'),
                    'args' => [
                        'file' => ['type' => Type::nonNull(Type::string())], // Base64 or URL
                        'name' => ['type' => Type::string()],
                        'destination' => ['type' => Type::string(), 'defaultValue' => 'public'],
                    ],
                    'resolve' => [new MediaResolver(), 'resolveUploadMedia'],
                ],
                'deleteMedia' => [
                    'type' => Type::boolean(),
                    'args' => [
                        'id' => ['type' => Type::nonNull(Type::int())],
                    ],
                    'resolve' => [new MediaResolver(), 'resolveDeleteMedia'],
                ],
            ],
        ];
        
        // Dispatch event to add custom mutation fields
        $event = new Event\MutationTypeBuildEvent($config, $this->types);
        $this->hookDispatcher->dispatch($event, 'graphql.mutation.fields');
        
        parent::__construct($config);
    }
}