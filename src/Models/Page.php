<?php

declare(strict_types=1);

namespace OOPress\Models;

use OOPress\Core\Database\Model;

class Page extends Model
{
    protected static string $table = 'pages';
    
    protected array $hidden = [];
    
    protected array $casts = [
        'id' => 'int',
        'parent_id' => 'int',
        'menu_order' => 'int',
        'show_in_menu' => 'bool',
        'author_id' => 'int',
        'created_at' => 'date',
        'updated_at' => 'date'
    ];
    
    /**
     * Get all pages for menu
     */
    public static function getMenuPages(): array
    {
        return self::query()
            ->where(['status' => 'published', 'show_in_menu' => 1])
            ->orderBy('menu_order', 'ASC')
            ->get();
    }
    
    /**
     * Get parent page
     */
    public function parent(): ?self
    {
        if ($this->parent_id > 0) {
            return self::find($this->parent_id);
        }
        return null;
    }
    
    /**
     * Get child pages
     */
    public function children(): array
    {
        return self::where(['parent_id' => $this->id, 'status' => 'published']);
    }
    
    /**
     * Check if page has children
     */
    public function hasChildren(): bool
    {
        return count($this->children()) > 0;
    }
    
    /**
     * Get page URL
     */
    public function getUrl(): string
    {
        if ($this->parent_id > 0) {
            $parent = $this->parent();
            return '/' . ($parent ? $parent->slug . '/' : '') . $this->slug;
        }
        return '/' . $this->slug;
    }
    
    /**
     * Get author
     */
    public function author(): ?User
    {
        return User::find($this->author_id);
    }
}