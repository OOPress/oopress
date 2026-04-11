<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Core\Auth;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class AuthController
{
    private Engine $view;
    private Auth $auth;
    
    public function __construct(Auth $auth)
    {
        $this->view = new Engine(__DIR__ . '/../../views');
        $this->auth = $auth;
    }
    
    public function showLogin(Request $request): Response
    {
        if ($this->auth->check()) {
            return Response::redirect('/dashboard');
        }
        
        $content = $this->view->render('auth/login', [
            'title' => 'Login',
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
            'title' => 'Login',
            'error' => 'Invalid username or password'
        ]);
        
        return new Response($content, 401);
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
        
        $content = $this->view->render('auth/dashboard', [
            'title' => 'Dashboard',
            'user' => $this->auth->user()
        ]);
        
        return new Response($content);
    }
}