<?php

declare(strict_types=1);

namespace OOPress\Block\Block;

use OOPress\Block\BlockInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * SystemMenuBlock — Example block that renders a menu.
 * 
 * @internal — Example block for demonstration
 */
class SystemMenuBlock implements BlockInterface
{
    public function getId(): string
    {
        return 'system_menu';
    }
    
    public function getLabel(): string
    {
        return 'Main Menu';
    }
    
    public function getDescription(): string
    {
        return 'Displays the main navigation menu';
    }
    
    public function getModule(): string
    {
        return 'oopress/system';
    }
    
    public function getCategory(): string
    {
        return 'Navigation';
    }
    
    public function render(Request $request, array $settings = []): string
    {
        $menuItems = [
            ['title' => 'Home', 'url' => '/'],
            ['title' => 'About', 'url' => '/about'],
            ['title' => 'Contact', 'url' => '/contact'],
        ];
        
        $html = '<ul class="menu">';
        foreach ($menuItems as $item) {
            $html .= sprintf(
                '<li><a href="%s">%s</a></li>',
                htmlspecialchars($item['url']),
                htmlspecialchars($item['title'])
            );
        }
        $html .= '</ul>';
        
        return $html;
    }
    
    public function getConfigForm(array $settings = []): array
    {
        return [
            'menu_name' => [
                'type' => 'select',
                'label' => 'Menu to display',
                'options' => [
                    'main' => 'Main Menu',
                    'footer' => 'Footer Menu',
                ],
                'default' => $settings['menu_name'] ?? 'main',
            ],
        ];
    }
    
    public function validateConfig(array $settings): array
    {
        $errors = [];
        
        if (empty($settings['menu_name'])) {
            $errors['menu_name'] = 'Menu name is required';
        }
        
        return $errors;
    }
    
    public function isCacheable(): bool
    {
        return true;
    }
    
    public function getCacheTags(): array
    {
        return ['menu'];
    }
    
    public function getCacheContexts(): array
    {
        return ['user.roles', 'url.path'];
    }
}
