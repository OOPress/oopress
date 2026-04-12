<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Comment extends Model
{
    protected static string $table = 'comments';
    
    protected array $casts = [
        'id' => 'int',
        'post_id' => 'int',
        'user_id' => 'int',
        'parent_id' => 'int',
        'likes' => 'int',
        'reported' => 'int',
        'created_at' => 'date',
        'updated_at' => 'date'
    ];
    
    public function post(): ?Post
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
    
    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    
    public function replies(): array
    {
        return self::where(['parent_id' => $this->id, 'status' => 'approved']);
    }
    
    public function approve(): void
    {
        $this->status = 'approved';
        $this->save();
    }
    
    public function markAsSpam(): void
    {
        $this->status = 'spam';
        $this->save();
    }
    
    public function trash(): void
    {
        $this->status = 'trash';
        $this->save();
    }
    
    public static function getPendingCount(): int
    {
        return count(self::where(['status' => 'pending']));
    }
    
    public static function getRecent(int $limit = 10): array
    {
        return self::query()
            ->where(['status' => 'pending'])
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get();
    }
}