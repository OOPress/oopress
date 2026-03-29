<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\ObjectType;
use GraphQL\Type\Definition\Type;

/**
 * UserType — GraphQL type for users.
 * 
 * @internal
 */
class UserType extends ObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'User',
            'description' => 'A user account',
            'fields' => [
                'id' => [
                    'type' => Type::nonNull(Type::id()),
                    'description' => 'The unique identifier',
                ],
                'username' => [
                    'type' => Type::string(),
                    'description' => 'Username',
                ],
                'email' => [
                    'type' => Type::string(),
                    'description' => 'Email address',
                ],
                'roles' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'User roles (ROLE_ADMIN, ROLE_EDITOR, etc.)',
                ],
                'status' => [
                    'type' => Type::string(),
                    'description' => 'Account status (active, blocked, etc.)',
                ],
                'created_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Account creation timestamp',
                ],
                'updated_at' => [
                    'type' => $types->get('DateTime'),
                    'description' => 'Last update timestamp',
                ],
                'content' => [
                    'type' => Type::listOf($types->get('Content')),
                    'description' => 'Content created by this user',
                    'resolve' => function($user, $args, $context) {
                        $contentResolver = new \OOPress\GraphQL\Resolver\ContentResolver(
                            $context['container']->get(\OOPress\Content\ContentRepository::class),
                            $context['container']->get(\OOPress\Content\ContentTypeManager::class),
                            $context['container']->get(\OOPress\Content\Query\ContentQuery::class),
                            $context['container']->get(\OOPress\Security\AuthorizationManager::class)
                        );
                        return $contentResolver->resolveContents(null, ['author_id' => $user->id], $context);
                    },
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}