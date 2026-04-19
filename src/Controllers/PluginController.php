<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Core\Plugin\PluginManager;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class PluginController
{
    private Engine $view;
    private PluginManager $pluginManager;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
        $this->pluginManager = new PluginManager();
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
        
        $plugins = $this->pluginManager->getAllPlugins();
        
        $content = $this->view->render('admin/plugins/index', [
            'title' => __('Plugins'),
            'plugins' => $plugins
        ]);
        
        return new Response($content);
    }
    
    public function activate(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $slug = $request->input('plugin');
        if ($this->pluginManager->activatePlugin($slug)) {
            $_SESSION['flash_success'] = __('Plugin activated successfully');
        } else {
            $_SESSION['flash_error'] = __('Failed to activate plugin');
        }
        
        return Response::redirect('/admin/plugins');
    }
    
    public function deactivate(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $slug = $request->input('plugin');
        if ($this->pluginManager->deactivatePlugin($slug)) {
            $_SESSION['flash_success'] = __('Plugin deactivated successfully');
        } else {
            $_SESSION['flash_error'] = __('Failed to deactivate plugin');
        }
        
        return Response::redirect('/admin/plugins');
    }
}