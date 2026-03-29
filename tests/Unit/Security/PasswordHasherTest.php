<?php

declare(strict_types=1);

namespace OOPress\Tests\Unit\Security;

use OOPress\Tests\TestCase;
use OOPress\Security\PasswordHasher;

/**
 * Test PasswordHasher functionality.
 * 
 * @internal
 */
class PasswordHasherTest extends TestCase
{
    private PasswordHasher $hasher;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->hasher = new PasswordHasher();
    }
    
    public function testHashPassword(): void
    {
        $password = 'TestPassword123!';
        $hash = $this->hasher->hash($password);
        
        $this->assertIsString($hash);
        $this->assertNotEquals($password, $hash);
        $this->assertGreaterThan(20, strlen($hash));
    }
    
    public function testVerifyPassword(): void
    {
        $password = 'TestPassword123!';
        $hash = $this->hasher->hash($password);
        
        $this->assertTrue($this->hasher->verify($password, $hash));
        $this->assertFalse($this->hasher->verify('WrongPassword', $hash));
    }
    
    public function testEmptyPasswordThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->hasher->hash('');
    }
    
    public function testShortPasswordThrowsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->hasher->hash('short');
    }
    
    public function testValidatePasswordStrength(): void
    {
        $weakPassword = 'weak';
        $strongPassword = 'StrongP@ss123';
        
        $errors = $this->hasher->validateStrength($weakPassword, [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_number' => true,
            'require_symbol' => true,
        ]);
        
        $this->assertNotEmpty($errors);
        
        $errors = $this->hasher->validateStrength($strongPassword, [
            'min_length' => 8,
            'require_uppercase' => true,
            'require_lowercase' => true,
            'require_number' => true,
        ]);
        
        $this->assertEmpty($errors);
    }
    
    public function testNeedsRehash(): void
    {
        $password = 'TestPassword123!';
        $hash = $this->hasher->hash($password);
        
        // Should not need rehash with default settings
        $this->assertFalse($this->hasher->needsRehash($hash));
    }
    
    public function testGenerateRandomPassword(): void
    {
        $password = $this->hasher->generateRandom(16, true);
        
        $this->assertEquals(16, strlen($password));
        
        $passwordNoSymbols = $this->hasher->generateRandom(10, false);
        $this->assertEquals(10, strlen($passwordNoSymbols));
    }
    
    public function testGetAlgorithm(): void
    {
        $algorithm = $this->hasher->getAlgorithm();
        
        $this->assertEquals('bcrypt', $algorithm);
    }
    
    public function testGetCost(): void
    {
        $cost = $this->hasher->getCost();
        
        $this->assertEquals(12, $cost);
    }
}