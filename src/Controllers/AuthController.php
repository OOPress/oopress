<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\User;
use OOPress\Core\Auth;
use OOPress\Core\Session;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class AuthController
{
    private Engine $view;
    private Auth $auth;
    private Session $session;
    
    public function __construct(Auth $auth, Session $session)
    {
        // Use theme view engine for dashboard, fallback to auth views for other templates
        $themePath = __DIR__ . '/../../public/themes/default/views';
        if (is_dir($themePath)) {
            $this->view = new Engine($themePath);
        } else {
            $this->view = new Engine(__DIR__ . '/../../views');
        }
        $this->auth = $auth;
        $this->session = $session;
    }
    
    public function showLogin(Request $request): Response
    {
        if ($this->auth->check()) {
            return Response::redirect('/dashboard');
        }
        
        $content = $this->view->render('auth/login', [
            'title' => __('Login'),
            'error' => ''
        ]);
        
        return new Response($content);
    }
    
    public function login(Request $request): Response
    {
        $username = $request->input('username');
        $password = $request->input('password');
        
        if ($this->auth->attempt($username, $password)) {
            return Response::redirect('/dashboard');
        }
        
        $content = $this->view->render('auth/login', [
            'title' => __('Login'),
            'error' => __('Invalid username or password')
        ]);
        
        return new Response($content, 401);
    }
    
    public function showRegister(Request $request): Response
    {
        if ($this->auth->check()) {
            return Response::redirect('/dashboard');
        }
        
        $content = $this->view->render('auth/register', [
            'title' => __('Register'),
            'error' => '',
            'success' => '',
            'old' => []
        ]);
        
        return new Response($content);
    }
    
    public function register(Request $request): Response
    {
        $username = trim($request->input('username'));
        $email = trim($request->input('email'));
        $password = $request->input('password');
        $passwordConfirm = $request->input('password_confirm');
        
        // Validation
        $errors = [];
        
        if (empty($username)) {
            $errors[] = __('Username is required');
        } elseif (strlen($username) < 3) {
            $errors[] = __('Username must be at least 3 characters');
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors[] = __('Username can only contain letters, numbers, and underscores');
        }
        
        if (empty($email)) {
            $errors[] = __('Email is required');
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = __('Invalid email address');
        }
        
        if (empty($password)) {
            $errors[] = __('Password is required');
        } elseif (strlen($password) < 6) {
            $errors[] = __('Password must be at least 6 characters');
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = __('Passwords do not match');
        }
        
        // Check if username exists
        $existingUser = User::firstWhere(['username' => $username]);
        if ($existingUser) {
            $errors[] = __('Username already taken');
        }
        
        // Check if email exists
        $existingEmail = User::firstWhere(['email' => $email]);
        if ($existingEmail) {
            $errors[] = __('Email already registered');
        }
        
        if (!empty($errors)) {
            $content = $this->view->render('auth/register', [
                'title' => __('Register'),
                'error' => implode('<br>', $errors),
                'success' => '',
                'old' => ['username' => $username, 'email' => $email]
            ]);
            
            return new Response($content, 400);
        }
        
        // Create user - direct database insert
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        $db = User::getDB();
        $result = $db->insert('users', [
            'username' => $username,
            'email' => $email,
            'password' => $hashedPassword,
            'display_name' => $username,
            'role' => 'subscriber',
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($result) {
            $content = $this->view->render('auth/register', [
                'title' => __('Register'),
                'error' => '',
                'success' => __('Registration successful! You can now login.'),
                'old' => []
            ]);
            
            return new Response($content);
        }
        
        $content = $this->view->render('auth/register', [
            'title' => __('Register'),
            'error' => __('Registration failed. Please try again.'),
            'success' => '',
            'old' => ['username' => $username, 'email' => $email]
        ]);
        
        return new Response($content, 500);
    }
    
    public function logout(Request $request): Response
    {
        $this->auth->logout();
        return Response::redirect('/');
    }
    
    public function dashboard(Request $request): Response
    {
        if (!$this->auth->check()) {
            return Response::redirect('/login');
        }
        
        $user = $this->auth->user();
        
        // Get stats for admin/editor
        $stats = [];
        if ($user->role === 'admin' || $user->role === 'editor') {
            $stats['total_posts'] = count(\OOPress\Models\Post::all());
            $stats['published_posts'] = count(\OOPress\Models\Post::where(['status' => 'published']));
            $stats['comments'] = count(\OOPress\Models\Comment::all());
        }
        
        $content = $this->view->render('dashboard', [
            'title' => __('Dashboard'),
            'user' => $user,
            'stats' => $stats
        ]);
        
        return new Response($content);
    }
}