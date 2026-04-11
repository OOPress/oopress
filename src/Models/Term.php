<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Term extends Model
{
    protected static string $table = 'terms';
    
    protected array $casts = [
        'id' => 'int',
        'taxonomy_id' => 'int'
    ];
    
    public function taxonomy(): ?Taxonomy
    {
        return $this->belongsTo(Taxonomy::class, 'taxonomy_id');
    }
}