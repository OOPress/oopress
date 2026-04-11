<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Media extends Model
{
    protected static string $table = 'media';
    
    protected array $casts = [
        'id' => 'int',
        'size' => 'int',
        'width' => 'int',
        'height' => 'int',
        'author_id' => 'int',
        'created_at' => 'date',
        'updated_at' => 'date'
    ];
    
    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}