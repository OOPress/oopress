<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class User extends Model
{
    protected static string $table = 'users';
    
    protected array $hidden = ['password'];
    
    protected array $casts = [
        'id' => 'int',
        'created_at' => 'date',
        'updated_at' => 'date',
        'last_login' => 'date'
    ];
    
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
}