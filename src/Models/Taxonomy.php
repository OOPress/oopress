<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Taxonomy extends Model
{
    protected static string $table = 'taxonomies';
    
    protected array $casts = [
        'id' => 'int',
        'hierarchical' => 'bool'
    ];
}