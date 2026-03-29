<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Type;

use GraphQL\Type\Definition\InputObjectType;
use GraphQL\Type\Definition\Type;

/**
 * UserInputType — GraphQL input type for creating/updating users.
 * 
 * @internal
 */
class UserInputType extends InputObjectType
{
    public function __construct(Types $types)
    {
        $config = [
            'name' => 'UserInput',
            'description' => 'Input for creating or updating users',
            'fields' => [
                'username' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Username',
                ],
                'email' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Email address',
                ],
                'password' => [
                    'type' => Type::nonNull(Type::string()),
                    'description' => 'Password (min 8 characters)',
                ],
                'roles' => [
                    'type' => Type::listOf(Type::string()),
                    'description' => 'User roles',
                ],
                'status' => [
                    'type' => Type::string(),
                    'description' => 'Account status',
                    'defaultValue' => 'active',
                ],
            ],
        ];
        
        parent::__construct($config);
    }
}