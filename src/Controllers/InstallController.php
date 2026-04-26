<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;
use Medoo\Medoo;

class InstallController
{
    private Engine $view;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
    }
    
    private function checkInstalled(): bool
    {
        return file_exists(__DIR__ . '/../../storage/installed.lock');
    }
    
    public function welcome(Request $request): Response
    {
        if ($this->checkInstalled()) {
            return new Response('Already installed', 403);
        }
        
        // Check PHP requirements only (no database)
        $requirements = $this->getPhpRequirements();
        $allPassed = true;
        foreach ($requirements as $req) {
            if (!$req['passed']) $allPassed = false;
        }
        
        $content = $this->view->render('install/welcome', [
            'title' => 'Installation - Welcome',
            'requirements' => $requirements,
            'all_passed' => $allPassed,
            'step' => 1
        ]);
        
        return new Response($content);
    }
    
    public function database(Request $request): Response
    {
        if ($this->checkInstalled()) {
            return new Response('Already installed', 403);
        }
        
        $error = null;
        
        if ($request->method() === 'POST') {
            $host = $request->input('db_host');
            $name = $request->input('db_name');
            $user = $request->input('db_user');
            $pass = $request->input('db_pass');
            
            try {
                // Test connection
                $pdo = new \PDO("mysql:host={$host}", $user, $pass);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                
                // Create database if not exists
                $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                
                $_SESSION['install']['db_host'] = $host;
                $_SESSION['install']['db_name'] = $name;
                $_SESSION['install']['db_user'] = $user;
                $_SESSION['install']['db_pass'] = $pass;
                
                return Response::redirect('/install/admin');
                
            } catch (\PDOException $e) {
                $error = $e->getMessage();
            }
        }
        
        $content = $this->view->render('install/database', [
            'title' => 'Installation - Database',
            'error' => $error,
            'step' => 2
        ]);
        
        return new Response($content);
    }
    
    public function admin(Request $request): Response
    {
        if ($this->checkInstalled()) {
            return new Response('Already installed', 403);
        }
        
        if (!isset($_SESSION['install']['db_host'])) {
            return Response::redirect('/install/database');
        }
        
        $error = null;
        
        if ($request->method() === 'POST') {
            $username = trim($request->input('username'));
            $email = trim($request->input('email'));
            $password = $request->input('password');
            $confirm = $request->input('password_confirm');
            
            if (empty($username)) {
                $error = 'Username is required';
            } elseif (strlen($username) < 3) {
                $error = 'Username must be at least 3 characters';
            } elseif (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Valid email is required';
            } elseif (empty($password)) {
                $error = 'Password is required';
            } elseif (strlen($password) < 6) {
                $error = 'Password must be at least 6 characters';
            } elseif ($password !== $confirm) {
                $error = 'Passwords do not match';
            }
            
            if (!$error) {
                $_SESSION['install']['admin_username'] = $username;
                $_SESSION['install']['admin_email'] = $email;
                $_SESSION['install']['admin_password'] = $password;
                
                return Response::redirect('/install/site');
            }
        }
        
        $content = $this->view->render('install/admin', [
            'title' => 'Installation - Admin Account',
            'error' => $error,
            'step' => 3
        ]);
        
        return new Response($content);
    }
    
    public function site(Request $request): Response
    {
        if ($this->checkInstalled()) {
            return new Response('Already installed', 403);
        }
        
        if (!isset($_SESSION['install']['db_host'])) {
            return Response::redirect('/install/database');
        }
        
        if ($request->method() === 'POST') {
            $_SESSION['install']['site_title'] = $request->input('site_title', 'OOPress');
            $_SESSION['install']['site_tagline'] = $request->input('site_tagline', 'A modern PHP CMS');
            $_SESSION['install']['timezone'] = $request->input('timezone', 'UTC');
            
            return Response::redirect('/install/run');
        }
        
        $content = $this->view->render('install/site', [
            'title' => 'Installation - Site Settings',
            'step' => 4,
            'timezones' => $this->getTimezones()
        ]);
        
        return new Response($content);
    }
    
    public function run(Request $request): Response
    {
        if ($this->checkInstalled()) {
            return new Response('Already installed', 403);
        }
        
        if (!isset($_SESSION['install']['db_host'])) {
            return Response::redirect('/install/database');
        }
        
        $error = null;
        $success = false;
        
        try {
            // Connect to database with Medoo
            $database = new Medoo([
                'type' => 'mysql',
                'host' => $_SESSION['install']['db_host'],
                'database' => $_SESSION['install']['db_name'],
                'username' => $_SESSION['install']['db_user'],
                'password' => $_SESSION['install']['db_pass'],
                'charset' => 'utf8mb4',
                'error' => \PDO::ERRMODE_EXCEPTION
            ]);
            
            // Run migrations
            $migrationFiles = glob(__DIR__ . '/../../database/migrations/*.php');
            sort($migrationFiles);
            
            foreach ($migrationFiles as $file) {
                $migration = require $file;
                if (method_exists($migration, 'up')) {
                    $migration->up($database);
                }
            }
            
            // Insert settings
            $database->insert('settings', [
                ['setting_key' => 'site_title', 'setting_value' => $_SESSION['install']['site_title'], 'setting_type' => 'text', 'setting_group' => 'general', 'setting_label' => 'Site Title'],
                ['setting_key' => 'site_tagline', 'setting_value' => $_SESSION['install']['site_tagline'], 'setting_type' => 'text', 'setting_group' => 'general', 'setting_label' => 'Tagline'],
                ['setting_key' => 'timezone', 'setting_value' => $_SESSION['install']['timezone'], 'setting_type' => 'text', 'setting_group' => 'general', 'setting_label' => 'Timezone']
            ]);
            
            // Create admin user
            $hashed = password_hash($_SESSION['install']['admin_password'], PASSWORD_BCRYPT);
            $database->insert('users', [
                'username' => $_SESSION['install']['admin_username'],
                'email' => $_SESSION['install']['admin_email'],
                'password' => $hashed,
                'display_name' => $_SESSION['install']['admin_username'],
                'role' => 'admin',
                'status' => 'active'
            ]);
            
            // Create .env file
            $env = "APP_ENV=production\n";
            $env .= "APP_DEBUG=false\n";
            $env .= "APP_TIMEZONE={$_SESSION['install']['timezone']}\n\n";
            $env .= "DB_TYPE=mysql\n";
            $env .= "DB_HOST={$_SESSION['install']['db_host']}\n";
            $env .= "DB_NAME={$_SESSION['install']['db_name']}\n";
            $env .= "DB_USER={$_SESSION['install']['db_user']}\n";
            $env .= "DB_PASS={$_SESSION['install']['db_pass']}\n";
            
            file_put_contents(__DIR__ . '/../../.env', $env);
            
            // Mark installed
            file_put_contents(__DIR__ . '/../../storage/installed.lock', date('Y-m-d H:i:s'));
            
            // Store admin username for display before clearing session
            $adminUsername = $_SESSION['install']['admin_username'];
            
            // Clear install session
            unset($_SESSION['install']);
            
            $success = true;
            
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        
        $content = $this->view->render('install/complete', [
            'title' => 'Installation - Complete',
            'success' => $success,
            'error' => $error,
            'step' => 5,
            'admin_username' => $adminUsername ?? null
        ]);
        
        return new Response($content);
    }
    
    private function getPhpRequirements(): array
    {
        return [
            ['name' => 'PHP Version 8.2+', 'current' => PHP_VERSION, 'required' => '8.2+', 'passed' => version_compare(PHP_VERSION, '8.2', '>=')],
            ['name' => 'PDO MySQL', 'current' => extension_loaded('pdo_mysql') ? 'Yes' : 'No', 'required' => 'Enabled', 'passed' => extension_loaded('pdo_mysql')],
            ['name' => 'JSON', 'current' => extension_loaded('json') ? 'Yes' : 'No', 'required' => 'Enabled', 'passed' => extension_loaded('json')],
            ['name' => 'MBString', 'current' => extension_loaded('mbstring') ? 'Yes' : 'No', 'required' => 'Enabled', 'passed' => extension_loaded('mbstring')],
            ['name' => 'OpenSSL', 'current' => extension_loaded('openssl') ? 'Yes' : 'No', 'required' => 'Enabled', 'passed' => extension_loaded('openssl')],
            ['name' => 'Fileinfo', 'current' => extension_loaded('fileinfo') ? 'Yes' : 'No', 'required' => 'Enabled', 'passed' => extension_loaded('fileinfo')],
            ['name' => 'cURL', 'current' => extension_loaded('curl') ? 'Yes' : 'No', 'required' => 'Enabled', 'passed' => extension_loaded('curl')]
        ];
    }
    
    private function getTimezones(): array
    {
        return [
            'UTC' => 'UTC',
            'America/New_York' => 'America/New_York',
            'America/Chicago' => 'America/Chicago',
            'America/Denver' => 'America/Denver',
            'America/Los_Angeles' => 'America/Los_Angeles',
            'Europe/London' => 'Europe/London',
            'Europe/Berlin' => 'Europe/Berlin',
            'Europe/Paris' => 'Europe/Paris',
            'Asia/Tokyo' => 'Asia/Tokyo',
            'Asia/Shanghai' => 'Asia/Shanghai',
            'Australia/Sydney' => 'Australia/Sydney'
        ];
    }
}