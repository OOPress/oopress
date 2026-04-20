<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Core\Installer;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class InstallController
{
    private Engine $view;
    private Installer $installer;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
        $this->installer = new Installer();
    }
    
    /**
     * Check if already installed
     */
    private function checkInstalled(): ?Response
    {
        if ($this->installer->isInstalled()) {
            return new Response('OOPress is already installed. Delete storage/installed.lock to reinstall.', 403);
        }
        return null;
    }
    
    /**
     * Step 1: Welcome & Requirements
     */
    public function welcome(Request $request): Response
    {
        $installed = $this->checkInstalled();
        if ($installed) return $installed;
        
        $requirements = $this->installer->getRequirements();
        $allPassed = $this->installer->checkRequirements();
        
        $content = $this->view->render('install/welcome', [
            'title' => 'Installation - Welcome',
            'requirements' => $requirements,
            'all_passed' => $allPassed,
            'step' => 1
        ]);
        
        return new Response($content);
    }
    
    /**
     * Step 2: Database Configuration
     */
    public function database(Request $request): Response
    {
        $installed = $this->checkInstalled();
        if ($installed) return $installed;
        
        $error = null;
        $success = null;
        
        if ($request->method() === 'POST') {
            $host = $request->input('db_host');
            $name = $request->input('db_name');
            $user = $request->input('db_user');
            $pass = $request->input('db_pass');
            
            $test = $this->installer->testDatabaseConnection($host, $name, $user, $pass);
            
            if ($test['success']) {
                $_SESSION['install']['db_host'] = $host;
                $_SESSION['install']['db_name'] = $name;
                $_SESSION['install']['db_user'] = $user;
                $_SESSION['install']['db_pass'] = $pass;
                
                return Response::redirect('/install/admin');
            } else {
                $error = $test['message'];
            }
        }
        
        $content = $this->view->render('install/database', [
            'title' => 'Installation - Database',
            'error' => $error,
            'success' => $success,
            'step' => 2
        ]);
        
        return new Response($content);
    }
    
    /**
     * Step 3: Admin User Creation
     */
    public function admin(Request $request): Response
    {
        $installed = $this->checkInstalled();
        if ($installed) return $installed;
        
        if (!isset($_SESSION['install']['db_host'])) {
            return Response::redirect('/install/database');
        }
        
        $error = null;
        
        if ($request->method() === 'POST') {
            $username = trim($request->input('username'));
            $email = trim($request->input('email'));
            $password = $request->input('password');
            $passwordConfirm = $request->input('password_confirm');
            
            $errors = [];
            
            if (empty($username)) {
                $errors[] = 'Username is required';
            } elseif (strlen($username) < 3) {
                $errors[] = 'Username must be at least 3 characters';
            }
            
            if (empty($email)) {
                $errors[] = 'Email is required';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            }
            
            if (empty($password)) {
                $errors[] = 'Password is required';
            } elseif (strlen($password) < 6) {
                $errors[] = 'Password must be at least 6 characters';
            }
            
            if ($password !== $passwordConfirm) {
                $errors[] = 'Passwords do not match';
            }
            
            if (empty($errors)) {
                $_SESSION['install']['admin_username'] = $username;
                $_SESSION['install']['admin_email'] = $email;
                $_SESSION['install']['admin_password'] = $password;
                
                return Response::redirect('/install/site');
            } else {
                $error = implode('<br>', $errors);
            }
        }
        
        $content = $this->view->render('install/admin', [
            'title' => 'Installation - Admin Account',
            'error' => $error,
            'step' => 3
        ]);
        
        return new Response($content);
    }
    
    /**
     * Step 4: Site Configuration
     */
    public function site(Request $request): Response
    {
        $installed = $this->checkInstalled();
        if ($installed) return $installed;
        
        if (!isset($_SESSION['install']['db_host'])) {
            return Response::redirect('/install/database');
        }
        
        if ($request->method() === 'POST') {
            $siteTitle = $request->input('site_title', 'OOPress');
            $siteTagline = $request->input('site_tagline', 'A modern PHP CMS');
            $timezone = $request->input('timezone', 'UTC');
            
            $_SESSION['install']['site_title'] = $siteTitle;
            $_SESSION['install']['site_tagline'] = $siteTagline;
            $_SESSION['install']['timezone'] = $timezone;
            
            return Response::redirect('/install/run');
        }
        
        $content = $this->view->render('install/site', [
            'title' => 'Installation - Site Settings',
            'timezones' => $this->getTimezones(),
            'step' => 4
        ]);
        
        return new Response($content);
    }
    
    /**
     * Step 5: Run Installation
     */
    public function run(Request $request): Response
    {
        $installed = $this->checkInstalled();
        if ($installed) return $installed;
        
        if (!isset($_SESSION['install']['db_host'])) {
            return Response::redirect('/install/database');
        }
        
        $error = null;
        $success = false;
        
        try {
            // Create database connection
            $dsn = "mysql:host={$_SESSION['install']['db_host']};dbname={$_SESSION['install']['db_name']};charset=utf8mb4";
            $pdo = new \PDO($dsn, $_SESSION['install']['db_user'], $_SESSION['install']['db_pass']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            // Create tables
            $errors = $this->installer->createTables($pdo);
            if (!empty($errors)) {
                throw new \Exception(implode('<br>', $errors));
            }
            
            // Insert default settings
            $this->installer->insertDefaultSettings($pdo);
            
            // Create admin user
            $this->installer->createAdminUser(
                $pdo,
                $_SESSION['install']['admin_username'],
                $_SESSION['install']['admin_email'],
                $_SESSION['install']['admin_password']
            );
            
            // Create .env file
            $envData = [
                'site_title' => $_SESSION['install']['site_title'],
                'timezone' => $_SESSION['install']['timezone'],
                'db_host' => $_SESSION['install']['db_host'],
                'db_name' => $_SESSION['install']['db_name'],
                'db_user' => $_SESSION['install']['db_user'],
                'db_pass' => $_SESSION['install']['db_pass']
            ];
            $this->installer->createEnvFile($envData);
            
            // Mark as installed
            $this->installer->markInstalled();
            
            // Clear session
            unset($_SESSION['install']);
            
            $success = true;
            
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }
        
        $content = $this->view->render('install/complete', [
            'title' => 'Installation - Complete',
            'success' => $success,
            'error' => $error,
            'step' => 5
        ]);
        
        return new Response($content);
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