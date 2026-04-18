<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Core\Theme\ThemeManager;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class ThemeController
{
    private Engine $view;
    private ThemeManager $themeManager;
    
    public function __construct(ThemeManager $themeManager)
    {
        $this->themeManager = $themeManager;
        $this->view = new Engine($this->themeManager->getThemeViewPath());
    }
    
    private function checkAdminAccess(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function index(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $themes = $this->themeManager->getAllThemes();
        
        $content = $this->view->render('admin/themes/index', [
            'title' => __('Themes'),
            'themes' => $themes,
            'active_theme' => $this->themeManager->getActiveTheme()
        ]);
        
        return new Response($content);
    }
    
    public function activate(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $theme = $request->input('theme');
        if ($this->themeManager->setActiveTheme($theme)) {
            $_SESSION['flash_success'] = __('Theme activated successfully');
        } else {
            $_SESSION['flash_error'] = __('Failed to activate theme');
        }
        
        return Response::redirect('/admin/themes');
    }
}