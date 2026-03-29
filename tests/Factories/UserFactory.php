<?php

declare(strict_types=1);

namespace OOPress\Tests\Factories;

use OOPress\Security\User;
use OOPress\Security\PasswordHasher;

/**
 * Factory for creating test users.
 * 
 * @internal
 */
class UserFactory
{
    private PasswordHasher $hasher;
    
    public function __construct()
    {
        $this->hasher = new PasswordHasher();
    }
    
    /**
     * Create a user for testing.
     */
    public function create(array $attributes = []): User
    {
        $defaults = [
            'id' => null,
            'username' => 'testuser_' . uniqid(),
            'email' => 'test_' . uniqid() . '@example.com',
            'password' => $this->hasher->hash('TestPassword123!'),
            'roles' => ['ROLE_USER'],
            'status' => 'active',
            'createdAt' => new \DateTimeImmutable(),
            'updatedAt' => new \DateTimeImmutable(),
        ];
        
        $data = array_merge($defaults, $attributes);
        
        return new User(
            id: $data['id'],
            username: $data['username'],
            email: $data['email'],
            password: $data['password'],
            roles: $data['roles'],
            status: $data['status'],
            createdAt: $data['createdAt'],
            updatedAt: $data['updatedAt']
        );
    }
    
    /**
     * Create an admin user.
     */
    public function createAdmin(array $attributes = []): User
    {
        return $this->create(array_merge([
            'username' => 'admin_' . uniqid(),
            'roles' => ['ROLE_ADMIN', 'ROLE_USER'],
        ], $attributes));
    }
    
    /**
     * Create a blocked user.
     */
    public function createBlocked(array $attributes = []): User
    {
        return $this->create(array_merge([
            'username' => 'blocked_' . uniqid(),
            'status' => 'blocked',
        ], $attributes));
    }
}