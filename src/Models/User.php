<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class User extends Model
{
    // Role constants
    public const ROLE_ADMIN = 'admin';
    public const ROLE_EDITOR = 'editor';
    public const ROLE_AUTHOR = 'author';
    public const ROLE_SUBSCRIBER = 'subscriber';
    
    // Status constants
    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_BANNED = 'banned';
    
    protected static string $table = 'users';
    
    protected array $hidden = ['password'];
    
    protected array $casts = [
        'id' => 'int',
        'created_at' => 'date',
        'updated_at' => 'date',
        'last_login' => 'date'
    ];

    public static function getDB(): \Medoo\Medoo
    {
        return static::$db;
    }

    public function updateLastLogin(): void
    {
        static::$db->update(static::$table, [
            'last_login' => date('Y-m-d H:i:s')
        ], ['id' => $this->id]);
        
        $this->attributes['last_login'] = date('Y-m-d H:i:s');
    }
    
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->password);
    }
    
    // Role check methods
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }
    
    public function isEditor(): bool
    {
        return $this->role === self::ROLE_EDITOR;
    }
    
    public function isAuthor(): bool
    {
        return $this->role === self::ROLE_AUTHOR;
    }
    
    public function canEditPost(Post $post): bool
    {
        if ($this->isAdmin() || $this->isEditor()) {
            return true;
        }
        
        if ($this->isAuthor() && $post->author_id === $this->id) {
            return true;
        }
        
        return false;
    }
    
    // Create new user
    public static function create(array $data): ?User
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        
        // Set defaults
        $data['role'] = $data['role'] ?? self::ROLE_SUBSCRIBER;
        $data['status'] = $data['status'] ?? self::STATUS_ACTIVE;
        
        $user = new self($data);
        
        if ($user->save()) {
            return $user;
        }
        
        return null;
    }
}