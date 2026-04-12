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
    
    // Taxonomy Methods using raw queries
    public function getCategories(): array
    {
        $db = static::$db;
        
        // Get category taxonomy ID
        $categoryTaxonomy = Taxonomy::firstWhere(['slug' => 'category']);
        if (!$categoryTaxonomy) {
            return [];
        }
        
        // Raw query to get categories for this post
        $sql = "SELECT t.* FROM terms t 
                INNER JOIN term_relationships tr ON t.id = tr.term_id 
                WHERE tr.object_id = :post_id 
                AND t.taxonomy_id = :taxonomy_id";
        
        $results = $db->query($sql, [
            ':post_id' => $this->id,
            ':taxonomy_id' => $categoryTaxonomy->id
        ])->fetchAll();
        
        return array_map(function($data) {
            return new Term((array)$data);
        }, $results);
    }

    public function getTags(): array
    {
        $db = static::$db;
        
        // Get tag taxonomy ID
        $tagTaxonomy = Taxonomy::firstWhere(['slug' => 'tag']);
        if (!$tagTaxonomy) {
            return [];
        }
        
        // Raw query to get tags for this post
        $sql = "SELECT t.* FROM terms t 
                INNER JOIN term_relationships tr ON t.id = tr.term_id 
                WHERE tr.object_id = :post_id 
                AND t.taxonomy_id = :taxonomy_id";
        
        $results = $db->query($sql, [
            ':post_id' => $this->id,
            ':taxonomy_id' => $tagTaxonomy->id
        ])->fetchAll();
        
        return array_map(function($data) {
            return new Term((array)$data);
        }, $results);
    }

    public function setCategories(array $categoryIds): void
    {
        $db = static::$db;
        
        // Get category taxonomy ID
        $categoryTaxonomy = Taxonomy::firstWhere(['slug' => 'category']);
        if (!$categoryTaxonomy) {
            return;
        }
        
        // Remove existing category relationships
        $deleteSql = "DELETE tr FROM term_relationships tr 
                    INNER JOIN terms t ON tr.term_id = t.id 
                    WHERE tr.object_id = :post_id 
                    AND t.taxonomy_id = :taxonomy_id";
        
        $db->query($deleteSql, [
            ':post_id' => $this->id,
            ':taxonomy_id' => $categoryTaxonomy->id
        ]);
        
        // Add new relationships
        foreach ($categoryIds as $termId) {
            $insertSql = "INSERT IGNORE INTO term_relationships (object_id, term_id) 
                        VALUES (:post_id, :term_id)";
            
            $db->query($insertSql, [
                ':post_id' => $this->id,
                ':term_id' => $termId
            ]);
        }
    }

    public function setTags(array $tagIds): void
    {
        $db = static::$db;
        
        // Get tag taxonomy ID
        $tagTaxonomy = Taxonomy::firstWhere(['slug' => 'tag']);
        if (!$tagTaxonomy) {
            return;
        }
        
        // Remove existing tag relationships
        $deleteSql = "DELETE tr FROM term_relationships tr 
                    INNER JOIN terms t ON tr.term_id = t.id 
                    WHERE tr.object_id = :post_id 
                    AND t.taxonomy_id = :taxonomy_id";
        
        $db->query($deleteSql, [
            ':post_id' => $this->id,
            ':taxonomy_id' => $tagTaxonomy->id
        ]);
        
        // Add new relationships
        foreach ($tagIds as $termId) {
            $insertSql = "INSERT IGNORE INTO term_relationships (object_id, term_id) 
                        VALUES (:post_id, :term_id)";
            
            $db->query($insertSql, [
                ':post_id' => $this->id,
                ':term_id' => $termId
            ]);
        }
    }

    public function getComments(): array
    {
        return Comment::where([
            'post_id' => $this->id,
            'status' => 'approved',
            'parent_id' => 0
        ]);
    }

    public function getCommentCount(): int
    {
        return count(Comment::where([
            'post_id' => $this->id,
            'status' => 'approved'
        ]));
    }
}