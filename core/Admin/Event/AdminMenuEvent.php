<?php

declare(strict_types=1);

namespace OOPress\Admin\Event;

use OOPress\Admin\AdminMenu;
use OOPress\Event\Event;

/**
 * AdminMenuEvent — Dispatched when building the admin menu.
 * 
 * Modules can listen to this event to add menu items.
 * 
 * @api
 */
class AdminMenuEvent extends Event
{
    public function __construct(
        private readonly AdminMenu $menu,
    ) {
        parent::__construct();
    }
    
    public function getMenu(): AdminMenu
    {
        return $this->menu;
    }
}