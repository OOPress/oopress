<?php

declare(strict_types=1);

namespace OOPress\Security;

use Doctrine\DBAL\Connection;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * UserProvider — Loads users from database.
 * 
 * @api
 */
class UserProvider implements UserProviderInterface
{
    public function __construct(
        private readonly Connection $connection,
    ) {}
    
    public function loadUserByIdentifier(string $identifier): SymfonyUserInterface
    {
        return $this->loadUserByUsername($identifier);
    }
    
    public function loadUserByUsername(string $username): SymfonyUserInterface
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM oop_users WHERE username = :username OR email = :username',
            ['username' => $username]
        );
        
        if (!$row) {
            throw new UsernameNotFoundException(sprintf('User "%s" not found.', $username));
        }
        
        return $this->hydrateUser($row);
    }
    
    public function loadUserById(int $id): ?UserInterface
    {
        $row = $this->connection->fetchAssociative(
            'SELECT * FROM oop_users WHERE id = :id',
            ['id' => $id]
        );
        
        if (!$row) {
            return null;
        }
        
        return $this->hydrateUser($row);
    }
    
    public function refreshUser(SymfonyUserInterface $user): SymfonyUserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }
        
        return $this->loadUserById($user->getId());
    }
    
    public function supportsClass(string $class): bool
    {
        return $class === User::class || is_subclass_of($class, User::class);
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