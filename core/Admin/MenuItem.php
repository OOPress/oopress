<?php

declare(strict_types=1);

namespace OOPress\Admin;

/**
 * MenuItem — A single item in the admin menu.
 * 
 * @api
 */
class MenuItem
{
    /**
     * @var array<MenuItem>
     */
    private array $children = [];
    
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $route,
        public readonly ?string $icon = null,
        public readonly int $weight = 0,
        public readonly ?string $parent = null,
        public readonly array $permissions = [],
    ) {}
    
    /**
     * Add a child menu item.
     */
    public function addChild(MenuItem $child): void
    {
        $this->children[] = $child;
    }
    
    /**
     * Get child menu items.
     * 
     * @return array<MenuItem>
     */
    public function getChildren(): array
    {
        // Sort by weight
        usort($this->children, fn($a, $b) => $a->weight <=> $b->weight);
        return $this->children;
    }
    
    /**
     * Get a child menu item by ID.
     */
    public function getChild(string $id): ?MenuItem
    {
        foreach ($this->children as $child) {
            if ($child->id === $id) {
                return $child;
            }
        }
        
        return null;
    }
    
    /**
     * Check if this item has children.
     */
    public function hasChildren(): bool
    {
        return !empty($this->children);
    }
    
    /**
     * Check if a user has permission to see this item.
     */
    public function hasPermission(array $userPermissions): bool
    {
        if (empty($this->permissions)) {
            return true;
        }
        
        return !empty(array_intersect($userPermissions, $this->permissions));
    }
}