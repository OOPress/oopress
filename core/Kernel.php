<?php

namespace OOPress;

use OOPress\Config\ConfigLoader;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Kernel
{
    private string $environment;
    private bool $debug;
    private ConfigLoader $config;
    private string $projectRoot;
    
    public function __construct(string $projectRoot, string $environment = 'prod', bool $debug = false)
    {
        $this->projectRoot = $projectRoot;
        $this->environment = $environment;
        $this->debug = $debug;
        $this->config = new ConfigLoader($projectRoot . '/config');
    }
    
    private function isInstalled(): bool
    {
        return $this->config->get('app.installed', false) === true;
    }
    
    public function handle(Request $request): Response
    {
        $path = $request->getPathInfo();
        $method = $request->getMethod();
        
        // Check if installed
        $isInstalled = $this->isInstalled();
        
        if (!$isInstalled && $path !== '/install' && !str_starts_with($path, '/install')) {
            return new Response('', Response::HTTP_FOUND, ['Location' => '/install']);
        }
        
        // Routes
        if ($path === '/') {
            return new Response('Welcome to ' . $this->config->get('site.name', 'OOPress'), Response::HTTP_OK);
        }
        
        if ($path === '/install') {
            if ($method === 'POST') {
                return $this->handleInstallation($request);
            }
            return $this->showInstaller();
        }
        
        if ($path === '/admin') {
            return new Response('Admin panel coming soon', Response::HTTP_OK);
        }
        
        return new Response('Page not found', Response::HTTP_NOT_FOUND);
    }
    
    private function showInstaller(): Response
    {
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>OOPress Installer</title>
            <style>
                body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
                input, select { width: 100%; padding: 8px; margin: 5px 0 15px; }
                button { background: #28a745; color: white; padding: 10px 20px; border: none; cursor: pointer; }
                .error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
                .success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px; }
            </style>
        </head>
        <body>
            <h1>Install OOPress</h1>
            <form method="POST" action="/install">
                <label>Site Name:</label>
                <input type="text" name="site_name" value="My OOPress Site" required>
                
                <label>Admin Username:</label>
                <input type="text" name="admin_username" value="admin" required>
                
                <label>Admin Email:</label>
                <input type="email" name="admin_email" required>
                
                <label>Admin Password:</label>
                <input type="password" name="admin_password" required>
                
                <label>Database Host:</label>
                <input type="text" name="db_host" value="localhost" required>
                
                <label>Database Name:</label>
                <input type="text" name="db_name" required>
                
                <label>Database User:</label>
                <input type="text" name="db_user" required>
                
                <label>Database Password:</label>
                <input type="password" name="db_password">
                
                <button type="submit">Install</button>
            </form>
        </body>
        </html>';
        
        return new Response($html, Response::HTTP_OK);
    }
    
    private function handleInstallation(Request $request): Response
    {
        $data = $request->request->all();
        
        // Validate required fields
        $required = ['site_name', 'admin_username', 'admin_email', 'admin_password', 'db_name', 'db_user'];
        $missing = [];
        
        foreach ($required as $field) {
            if (empty($data[$field])) {
                $missing[] = $field;
            }
        }
        
        if (!empty($missing)) {
            $errorHtml = '<div class="error">Missing: ' . implode(', ', $missing) . '</div>';
            return new Response($errorHtml . $this->showInstaller()->getContent(), Response::HTTP_BAD_REQUEST);
        }
        
        // Save database config to database.php
        $dbConfigFile = $this->projectRoot . '/config/database.php';
        $this->config->saveConfigFile($dbConfigFile, [
            'database' => [
                'host' => $data['db_host'],
                'name' => $data['db_name'],
                'user' => $data['db_user'],
                'password' => $data['db_password'],
            ]
        ]);
        
        // Save app config to app.php
        $appConfigFile = $this->projectRoot . '/config/app.php';
        $this->config->saveConfigFile($appConfigFile, [
            'app' => [
                'installed' => true,
                'installed_at' => date('Y-m-d H:i:s'),
            ],
            'site' => [
                'name' => $data['site_name'],
                'url' => $data['site_url'] ?? 'http://localhost',
            ]
        ]);
        
        $html = '<!DOCTYPE html>
        <html>
        <head>
            <title>Installation Complete</title>
            <style>
                body { font-family: sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; text-align: center; }
                .success { background: #d4edda; color: #155724; padding: 20px; border-radius: 4px; }
                button { background: #007bff; color: white; padding: 10px 20px; border: none; cursor: pointer; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="success">
                <h2>✓ Installation Complete!</h2>
                <p>OOPress has been installed successfully.</p>
                <a href="/"><button>Go to Homepage</button></a>
                <a href="/admin"><button>Go to Admin Panel</button></a>
            </div>
        </body>
        </html>';
        
        return new Response($html, Response::HTTP_OK);
    }
}