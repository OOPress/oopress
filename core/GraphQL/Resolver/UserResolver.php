<?php

declare(strict_types=1);

namespace OOPress\GraphQL\Resolver;

use Doctrine\DBAL\Connection;
use OOPress\Security\UserProvider;
use OOPress\Security\PasswordHasher;
use OOPress\Security\AuthorizationManager;
use OOPress\Security\User;
use OOPress\Security\UserInterface;

/**
 * UserResolver — Resolves user-related GraphQL fields.
 * 
 * @internal
 */
class UserResolver
{
    public function __construct(
        private readonly Connection $connection,
        private readonly UserProvider $userProvider,
        private readonly PasswordHasher $passwordHasher,
        private readonly AuthorizationManager $authorization,
    ) {}
    
    /**
     * Resolve single user.
     */
    public function resolveUser($root, array $args, array $context): ?UserInterface
    {
        $currentUser = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($currentUser, 'view_user', $args['id'] ?? null)) {
            return null;
        }
        
        if (isset($args['id'])) {
            return $this->userProvider->loadUserById((int) $args['id']);
        } elseif (isset($args['username'])) {
            try {
                return $this->userProvider->loadUserByUsername($args['username']);
            } catch (\Exception $e) {
                return null;
            }
        }
        
        return null;
    }
    
    /**
     * Resolve multiple users.
     */
    public function resolveUsers($root, array $args, array $context): array
    {
        $currentUser = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($currentUser, 'list_users')) {
            return [];
        }
        
        $rows = $this->connection->fetchAllAssociative(
            'SELECT * FROM oop_users LIMIT :limit OFFSET :offset',
            ['limit' => $args['limit'], 'offset' => $args['offset']]
        );
        
        $users = [];
        foreach ($rows as $row) {
            $users[] = $this->hydrateUser($row);
        }
        
        return $users;
    }
    
    /**
     * Resolve current user.
     */
    public function resolveMe($root, array $args, array $context): ?UserInterface
    {
        return $context['user'] ?? null;
    }
    
    /**
     * Resolve create user mutation.
     */
    public function resolveCreateUser($root, array $args, array $context): ?UserInterface
    {
        $currentUser = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($currentUser, 'create_user')) {
            throw new \RuntimeException('Access denied');
        }
        
        $input = $args['input'];
        
        // Check if user exists
        $exists = $this->connection->fetchOne(
            'SELECT id FROM oop_users WHERE username = :username OR email = :email',
            ['username' => $input['username'], 'email' => $input['email']]
        );
        
        if ($exists) {
            throw new \RuntimeException('User already exists');
        }
        
        // Hash password
        $hashedPassword = $this->passwordHasher->hash($input['password']);
        
        // Create user
        $this->connection->insert('oop_users', [
            'username' => $input['username'],
            'email' => $input['email'],
            'password' => $hashedPassword,
            'roles' => json_encode($input['roles'] ?? ['ROLE_USER']),
            'status' => $input['status'] ?? 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        
        $userId = $this->connection->lastInsertId();
        
        return $this->userProvider->loadUserById($userId);
    }
    
    /**
     * Resolve update user mutation.
     */
    public function resolveUpdateUser($root, array $args, array $context): ?UserInterface
    {
        $currentUser = $context['user'] ?? null;
        $targetUser = $this->userProvider->loadUserById((int) $args['id']);
        
        if (!$targetUser) {
            throw new \RuntimeException('User not found');
        }
        
        if (!$this->authorization->isGranted($currentUser, 'edit_user', $targetUser)) {
            throw new \RuntimeException('Access denied');
        }
        
        $input = $args['input'];
        $updateData = [];
        
        if (isset($input['email'])) {
            $updateData['email'] = $input['email'];
        }
        
        if (isset($input['password'])) {
            $updateData['password'] = $this->passwordHasher->hash($input['password']);
        }
        
        if ($this->authorization->isGranted($currentUser, 'edit_user_roles')) {
            if (isset($input['roles'])) {
                $updateData['roles'] = json_encode($input['roles']);
            }
            if (isset($input['status'])) {
                $updateData['status'] = $input['status'];
            }
        }
        
        if (!empty($updateData)) {
            $updateData['updated_at'] = date('Y-m-d H:i:s');
            $this->connection->update('oop_users', $updateData, ['id' => $targetUser->getId()]);
        }
        
        return $this->userProvider->loadUserById($targetUser->getId());
    }
    
    /**
     * Resolve delete user mutation.
     */
    public function resolveDeleteUser($root, array $args, array $context): bool
    {
        $currentUser = $context['user'] ?? null;
        
        if (!$this->authorization->isGranted($currentUser, 'delete_user')) {
            throw new \RuntimeException('Access denied');
        }
        
        $affected = $this->connection->delete('oop_users', ['id' => $args['id']]);
        
        return $affected > 0;
    }
    
    private function hydrateUser(array $row): User
    {
        return new User(
            id: (int) $row['id'],
            username: $row['username'],
            email: $row['email'],
            password: $row['password'],
            roles: json_decode($row['roles'] ?? '["ROLE_USER"]', true),
            status: $row['status'],
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: new \DateTimeImmutable($row['updated_at']),
        );
    }
}