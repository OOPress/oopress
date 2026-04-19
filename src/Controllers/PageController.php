<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Page;
use OOPress\Models\Setting;
use OOPress\Core\Theme\ThemeManager;
use OOPress\Core\Auth;
use OOPress\Core\SEO;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class PageController
{
    private Engine $view;
    
    public function __construct()
    {
        $themeManager = new ThemeManager();
        $this->view = new Engine($themeManager->getThemeViewPath());
    }
    
    public function show(Request $request): Response
    {
        $slug = $request->attribute('slug');
        
        // Find page by slug
        $page = Page::firstWhere(['slug' => $slug, 'status' => 'published']);
        
        if (!$page) {
            $seo = new SEO();
            $seo->set404();
            
            $content = $this->view->render('errors/404', [
                'title' => 'Page Not Found',
                'seo' => $seo
            ]);
            return new Response($content, 404);
        }
        
        // Get auth
        $auth = null;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) {
            $auth = new Auth(new \OOPress\Core\Session());
        }
        
        // Get SEO
        $seo = new SEO();
        $seo->setArchive($page->title, $page->excerpt ?? '');
        
        // Get theme asset URL
        $themeManager = new ThemeManager();
        $themeAssetUrl = $themeManager->getThemeAssetUrl('');
        
        // Check for custom template
        $template = $page->page_template;
        $templateFile = $template === 'default' ? 'page' : 'page-' . $template;
        
        $content = $this->view->render($templateFile, [
            'title' => $page->title,
            'page' => $page,
            'content' => $page->content,
            'auth' => $auth,
            'seo' => $seo,
            'theme_asset_url' => $themeAssetUrl,
            'site_title' => Setting::get('site_title', 'OOPress'),
            'children' => $page->children()
        ]);
        
        return new Response($content);
    }
}