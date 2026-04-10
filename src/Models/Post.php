<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Post extends Model
{
    protected static string $table = 'posts';
    
    protected array $hidden = ['password'];
    
    protected array $casts = [
        'id' => 'int',
        'status' => 'string',
        'created_at' => 'date'
    ];
}