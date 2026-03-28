<?php

declare(strict_types=1);

namespace OOPress\Security;

/**
 * User — User entity implementation.
 * 
 * @api
 */
class User implements UserInterface
{
    public function __construct(
        private ?int $id,
        private string $username,
        private string $email,
        private string $password,
        private array $roles,
        private string $status,
        private \DateTimeImmutable $createdAt,
        private \DateTimeImmutable $updatedAt,
    ) {}
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getUsername(): string
    {
        return $this->username;
    }
    
    public function getEmail(): string
    {
        return $this->email;
    }
    
    public function getPassword(): string
    {
        return $this->password;
    }
    
    public function getSalt(): ?string
    {
        return null; // Using bcrypt
    }
    
    public function getRoles(): array
    {
        return $this->roles;
    }
    
    public function getStatus(): string
    {
        return $this->status;
    }
    
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
    
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
    
    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
    
    public function eraseCredentials(): void
    {
        // No sensitive data to erase
    }
    
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
    
    public function setPassword(string $password): void
    {
        $this->password = $password;
    }
    
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }
    
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }
}