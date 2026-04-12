<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Setting;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class SettingsController
{
    private Engine $view;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
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
        
        $group = $request->input('group', 'general');
        $settings = Setting::getAllGrouped();
        
        // Get flash messages from session
        $success = $_SESSION['_flash']['success'] ?? null;
        $error = $_SESSION['_flash']['error'] ?? null;
        unset($_SESSION['_flash']['success'], $_SESSION['_flash']['error']);
        
        $content = $this->view->render('admin/settings/index', [
            'title' => __('Site Settings'),
            'settings' => $settings,
            'current_group' => $group,
            'success' => $success,
            'error' => $error
        ]);
        
        return new Response($content);
    }
    
    public function save(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        if ($request->method() === 'POST') {
            $settings = $request->all();
            $group = $request->input('_group', 'general');
            
            unset($settings['_group']);
            
            foreach ($settings as $key => $value) {
                Setting::set($key, $value);
            }
            
            Setting::clearCache();
            
            // Set success message in session
            $_SESSION['_flash']['success'] = __('Settings saved successfully!');
            
            // Redirect back to settings page
            return Response::redirect('/admin/settings?group=' . urlencode($group));
        }
        
        return Response::redirect('/admin/settings');
    }
}