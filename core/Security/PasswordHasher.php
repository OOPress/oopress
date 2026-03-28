<?php

declare(strict_types=1);

namespace OOPress\Security;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * PasswordHasher — Handles password hashing.
 * 
 * @api
 */
class PasswordHasher implements UserPasswordHasherInterface
{
    private PasswordHasherFactory $factory;
    
    public function __construct()
    {
        $this->factory = new PasswordHasherFactory([
            'common' => ['algorithm' => 'bcrypt', 'cost' => 12],
        ]);
    }
    
    public function hashPassword($user, string $plainPassword): string
    {
        $hasher = $this->factory->getPasswordHasher('common');
        return $hasher->hash($plainPassword);
    }
    
    public function isPasswordValid($user, string $plainPassword): bool
    {
        $hasher = $this->factory->getPasswordHasher('common');
        return $hasher->verify($user->getPassword(), $plainPassword);
    }
    
    public function needsRehash($user): bool
    {
        $hasher = $this->factory->getPasswordHasher('common');
        return $hasher->needsRehash($user->getPassword());
    }
}