<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Post extends Model
{
    protected static string $table = 'posts';
    
    protected array $hidden = [];
    
    protected array $casts = [
        'id' => 'int',
        'views' => 'int',
        'author_id' => 'int',
        'published_at' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date'
    ];
    
    public function author(): ?User
    {
        return $this->belongsTo(User::class, 'author_id');
    }
    
    public function categories(): array
    {
        // Will implement after taxonomy system
        return [];
    }
    
    public function incrementViews(): void
    {
        static::$db->update(static::$table, [
            'views[+]' => 1
        ], ['id' => $this->id]);
        
        $this->attributes['views']++;
    }
    
    public static function getPublished(): array
    {
        return static::where([
            'status' => 'published',
            'published_at[<=]' => date('Y-m-d H:i:s')
        ]);
    }
    
    public static function getRecent(int $limit = 10): array
    {
        return static::query()
            ->where(['status' => 'published'])
            ->orderBy('published_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}