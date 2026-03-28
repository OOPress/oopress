<?php

declare(strict_types=1);

namespace OOPress\Admin;

use OOPress\Event\HookDispatcher;

/**
 * AdminMenu — Manages the admin panel menu structure.
 * 
 * @api
 */
class AdminMenu
{
    /**
     * @var array<MenuItem>
     */
    private array $items = [];
    
    public function __construct(
        private readonly HookDispatcher $hookDispatcher,
    ) {
        $this->registerCoreMenuItems();
    }
    
    /**
     * Register core admin menu items.
     */
    private function registerCoreMenuItems(): void
    {
        // Dashboard
        $this->addItem(new MenuItem(
            id: 'dashboard',
            label: 'Dashboard',
            route: 'admin.dashboard',
            icon: 'dashboard',
            weight: 0,
        ));
        
        // Content
        $content = new MenuItem(
            id: 'content',
            label: 'Content',
            route: 'admin.content',
            icon: 'content',
            weight: 10,
        );
        $content->addChild(new MenuItem(
            id: 'content_list',
            label: 'All Content',
            route: 'admin.content.list',
            weight: 0,
        ));
        $content->addChild(new MenuItem(
            id: 'content_add',
            label: 'Add Content',
            route: 'admin.content.add',
            weight: 10,
        ));
        $content->addChild(new MenuItem(
            id: 'content_types',
            label: 'Content Types',
            route: 'admin.content.types',
            weight: 20,
        ));
        $this->addItem($content);
        
        // Structure
        $structure = new MenuItem(
            id: 'structure',
            label: 'Structure',
            route: 'admin.structure',
            icon: 'structure',
            weight: 20,
        );
        $structure->addChild(new MenuItem(
            id: 'blocks',
            label: 'Blocks',
            route: 'admin.structure.blocks',
            weight: 0,
        ));
        $structure->addChild(new MenuItem(
            id: 'menus',
            label: 'Menus',
            route: 'admin.structure.menus',
            weight: 10,
        ));
        $this->addItem($structure);
        
        // Appearance
        $appearance = new MenuItem(
            id: 'appearance',
            label: 'Appearance',
            route: 'admin.appearance',
            icon: 'appearance',
            weight: 30,
        );
        $appearance->addChild(new MenuItem(
            id: 'themes',
            label: 'Themes',
            route: 'admin.appearance.themes',
            weight: 0,
        ));
        $this->addItem($appearance);
        
        // Modules
        $modules = new MenuItem(
            id: 'modules',
            label: 'Modules',
            route: 'admin.modules',
            icon: 'modules',
            weight: 40,
        );
        $modules->addChild(new MenuItem(
            id: 'modules_list',
            label: 'List Modules',
            route: 'admin.modules.list',
            weight: 0,
        ));
        $modules->addChild(new MenuItem(
            id: 'modules_update',
            label: 'Updates',
            route: 'admin.modules.update',
            weight: 10,
        ));
        $this->addItem($modules);
        
        // Users
        $users = new MenuItem(
            id: 'users',
            label: 'Users',
            route: 'admin.users',
            icon: 'users',
            weight: 50,
        );
        $users->addChild(new MenuItem(
            id: 'users_list',
            label: 'List Users',
            route: 'admin.users.list',
            weight: 0,
        ));
        $users->addChild(new MenuItem(
            id: 'users_add',
            label: 'Add User',
            route: 'admin.users.add',
            weight: 10,
        ));
        $users->addChild(new MenuItem(
            id: 'roles',
            label: 'Roles',
            route: 'admin.users.roles',
            weight: 20,
        ));
        $this->addItem($users);
        
        // Configuration
        $config = new MenuItem(
            id: 'config',
            label: 'Configuration',
            route: 'admin.config',
            icon: 'config',
            weight: 60,
        );
        $config->addChild(new MenuItem(
            id: 'system',
            label: 'System',
            route: 'admin.config.system',
            weight: 0,
        ));
        $config->addChild(new MenuItem(
            id: 'site_info',
            label: 'Site Information',
            route: 'admin.config.site',
            weight: 10,
        ));
        $config->addChild(new MenuItem(
            id: 'languages',
            label: 'Languages',
            route: 'admin.config.languages',
            weight: 20,
        ));
        $this->addItem($config);
        
        // Reports
        $reports = new MenuItem(
            id: 'reports',
            label: 'Reports',
            route: 'admin.reports',
            icon: 'reports',
            weight: 70,
        );
        $reports->addChild(new MenuItem(
            id: 'status',
            label: 'Status Report',
            route: 'admin.reports.status',
            weight: 0,
        ));
        $reports->addChild(new MenuItem(
            id: 'modules_health',
            label: 'Module Health',
            route: 'admin.reports.modules',
            weight: 10,
        ));
        $reports->addChild(new MenuItem(
            id: 'logs',
            label: 'Logs',
            route: 'admin.reports.logs',
            weight: 20,
        ));
        $this->addItem($reports);
        
        // Dispatch event for modules to add menu items
        $event = new Event\AdminMenuEvent($this);
        $this->hookDispatcher->dispatch($event, 'admin.menu.build');
    }
    
    /**
     * Add a menu item.
     */
    public function addItem(MenuItem $item): void
    {
        $this->items[] = $item;
    }
    
    /**
     * Get all menu items.
     * 
     * @return array<MenuItem>
     */
    public function getItems(): array
    {
        // Sort by weight
        usort($this->items, fn($a, $b) => $a->weight <=> $b->weight);
        return $this->items;
    }
    
    /**
     * Get menu item by ID.
     */
    public function getItem(string $id): ?MenuItem
    {
        foreach ($this->items as $item) {
            if ($item->id === $id) {
                return $item;
            }
            
            $child = $item->getChild($id);
            if ($child) {
                return $child;
            }
        }
        
        return null;
    }
    
    /**
     * Check if a menu item is active for a given route.
     */
    public function isActive(string $route, string $currentRoute): bool
    {
        return $route === $currentRoute;
    }
}
