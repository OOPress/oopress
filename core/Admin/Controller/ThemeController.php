<?php

declare(strict_types=1);

namespace OOPress\Admin\Controller;

use OOPress\Extension\ExtensionLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ThemeController — Theme management pages.
 * 
 * @internal
 */
class ThemeController
{
    public function __construct(
        private readonly ExtensionLoader $extensionLoader,
    ) {}
    
    /**
     * List themes.
     */
    public function list(Request $request): Response
    {
        $themes = [];
        
        foreach ($this->extensionLoader->getThemes() as $themeId => $manifest) {
            $themes[$themeId] = [
                'id' => $themeId,
                'name' => $manifest->name,
                'version' => $manifest->version,
                'description' => $manifest->description,
                'active' => $themeId === 'default', // Placeholder
                'screenshot' => "/themes/$themeId/screenshot.png",
            ];
        }
        
        $content = $this->renderTemplate('admin/themes/list.html.twig', [
            'themes' => $themes,
        ]);
        
        return new Response($content);
    }
    
    /**
     * Activate theme.
     */
    public function activate(string $themeId, Request $request): Response
    {
        // Placeholder - will set active theme in config
        return new RedirectResponse('/admin/appearance/themes');
    }
    
    /**
     * Theme settings.
     */
    public function settings(string $themeId, Request $request): Response
    {
        $theme = $this->extensionLoader->getTheme($themeId);
        
        if (!$theme) {
            return new Response('Theme not found', Response::HTTP_NOT_FOUND);
        }
        
        $content = $this->renderTemplate('admin/themes/settings.html.twig', [
            'theme' => $theme,
            'settings' => [], // Placeholder - load theme settings
        ]);
        
        return new Response($content);
    }
    
    /**
     * Render a template.
     */
    private function renderTemplate(string $template, array $variables = []): string
    {
        // Placeholder - will use Twig when integrated
        return '<h1>Theme Management</h1><pre>' . print_r($variables, true) . '</pre>';
    }
}