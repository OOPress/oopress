<?php

declare(strict_types=1);

namespace OOPress\Security;

use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * UserInterface — Contract for user objects.
 * 
 * @api
 */
interface UserInterface extends SymfonyUserInterface
{
    public function getId(): ?int;
    public function getUsername(): string;
    public function getEmail(): string;
    public function getStatus(): string;
    public function isActive(): bool;
    public function getCreatedAt(): \DateTimeImmutable;
    public function getUpdatedAt(): \DateTimeImmutable;
}