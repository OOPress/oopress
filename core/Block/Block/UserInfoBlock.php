<?php

declare(strict_types=1);

namespace OOPress\Block\Block;

use OOPress\Block\BlockInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * UserInfoBlock — Example block showing user information.
 * 
 * @internal — Example block for demonstration
 */
class UserInfoBlock implements BlockInterface
{
    public function getId(): string
    {
        return 'user_info';
    }
    
    public function getLabel(): string
    {
        return 'User Info';
    }
    
    public function getDescription(): string
    {
        return 'Displays current user information and login/logout links';
    }
    
    public function getModule(): string
    {
        return 'oopress/system';
    }
    
    public function getCategory(): string
    {
        return 'User';
    }
    
    public function render(Request $request, array $settings = []): string
    {
        $session = $request->getSession();
        $username = $session->get('username', 'Guest');
        $isLoggedIn = $session->has('user_id');
        
        if ($isLoggedIn) {
            $html = sprintf(
                '<div class="user-info">Welcome, %s! <a href="/logout">Log out</a></div>',
                htmlspecialchars($username)
            );
        } else {
            $html = '<div class="user-info"><a href="/login">Log in</a> | <a href="/register">Register</a></div>';
        }
        
        return $html;
    }
    
    public function getConfigForm(array $settings = []): array
    {
        return [
            'show_avatar' => [
                'type' => 'boolean',
                'label' => 'Show user avatar',
                'default' => $settings['show_avatar'] ?? false,
            ],
            'greeting' => [
                'type' => 'text',
                'label' => 'Greeting text',
                'default' => $settings['greeting'] ?? 'Welcome',
            ],
        ];
    }
    
    public function validateConfig(array $settings): array
    {
        return [];
    }
    
    public function isCacheable(): bool
    {
        return false; // User info changes per user
    }
    
    public function getCacheTags(): array
    {
        return [];
    }
    
    public function getCacheContexts(): array
    {
        return ['user'];
    }
}
