<?php

declare(strict_types=1);

namespace OOPress\Security;

use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactory;
use Symfony\Component\PasswordHasher\PasswordHasherInterface as SymfonyPasswordHasherInterface;

/**
 * PasswordHasher — Standalone password hashing service.
 * 
 * This class handles password hashing and verification without requiring
 * a User object, making it more flexible for use in APIs, CLI commands,
 * and anywhere else password operations are needed.
 * 
 * @api
 */
class PasswordHasher
{
    private SymfonyPasswordHasherInterface $hasher;
    private string $algorithm;
    private int $cost;
    
    /**
     * @param string $algorithm Hashing algorithm (bcrypt, argon2i, argon2id, sodium)
     * @param int $cost Algorithm cost factor (higher = more secure but slower)
     */
    public function __construct(
        string $algorithm = 'bcrypt',
        int $cost = 12,
    ) {
        $this->algorithm = $algorithm;
        $this->cost = $cost;
        
        $factory = new PasswordHasherFactory([
            'default' => [
                'algorithm' => $algorithm,
                'cost' => $cost,
            ],
        ]);
        
        $this->hasher = $factory->getPasswordHasher('default');
    }
    
    /**
     * Hash a plain text password.
     * 
     * @param string $plainPassword The plain text password
     * @return string The hashed password
     */
    public function hash(string $plainPassword): string
    {
        if (empty($plainPassword)) {
            throw new \InvalidArgumentException('Password cannot be empty');
        }
        
        if (strlen($plainPassword) < 8) {
            throw new \InvalidArgumentException('Password must be at least 8 characters');
        }
        
        return $this->hasher->hash($plainPassword);
    }
    
    /**
     * Verify a password against a hash.
     * 
     * @param string $plainPassword The plain text password to verify
     * @param string $hashedPassword The stored hash to verify against
     * @return bool True if password matches, false otherwise
     */
    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        if (empty($plainPassword) || empty($hashedPassword)) {
            return false;
        }
        
        return $this->hasher->verify($hashedPassword, $plainPassword);
    }
    
    /**
     * Check if a hash needs to be rehashed (e.g., if algorithm or cost changed).
     * 
     * @param string $hashedPassword The stored hash to check
     * @return bool True if rehash is needed, false otherwise
     */
    public function needsRehash(string $hashedPassword): bool
    {
        return $this->hasher->needsRehash($hashedPassword);
    }
    
    /**
     * Get the algorithm being used.
     */
    public function getAlgorithm(): string
    {
        return $this->algorithm;
    }
    
    /**
     * Get the cost factor being used.
     */
    public function getCost(): int
    {
        return $this->cost;
    }
    
    /**
     * Validate password strength.
     * 
     * @param string $password The password to validate
     * @param array $requirements Password requirements
     * @return array<string, string> Validation errors
     */
    public function validateStrength(string $password, array $requirements = []): array
    {
        $errors = [];
        
        $minLength = $requirements['min_length'] ?? 8;
        if (strlen($password) < $minLength) {
            $errors['length'] = sprintf('Password must be at least %d characters', $minLength);
        }
        
        if ($requirements['require_uppercase'] ?? false) {
            if (!preg_match('/[A-Z]/', $password)) {
                $errors['uppercase'] = 'Password must contain at least one uppercase letter';
            }
        }
        
        if ($requirements['require_lowercase'] ?? false) {
            if (!preg_match('/[a-z]/', $password)) {
                $errors['lowercase'] = 'Password must contain at least one lowercase letter';
            }
        }
        
        if ($requirements['require_number'] ?? false) {
            if (!preg_match('/[0-9]/', $password)) {
                $errors['number'] = 'Password must contain at least one number';
            }
        }
        
        if ($requirements['require_symbol'] ?? false) {
            if (!preg_match('/[^a-zA-Z0-9]/', $password)) {
                $errors['symbol'] = 'Password must contain at least one special character';
            }
        }
        
        return $errors;
    }
    
    /**
     * Generate a random password.
     * 
     * @param int $length Password length
     * @param bool $includeSymbols Whether to include special characters
     * @return string Random password
     */
    public function generateRandom(int $length = 16, bool $includeSymbols = true): string
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        
        if ($includeSymbols) {
            $chars .= '!@#$%^&*()_+-=[]{}|;:,.<>?';
        }
        
        $password = '';
        $max = strlen($chars) - 1;
        
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $max)];
        }
        
        return $password;
    }
}