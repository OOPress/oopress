<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Post;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class HomeController
{
    private Engine $view;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
    }
    
    public function index(Request $request): Response
    {
        $posts = Post::where(['status' => 'published']);
        
        $content = $this->view->render('home', [
            'posts' => $posts,
            'title' => __('Welcome to OOPress')
        ]);
        
        return new Response($content);
    }
    
    public function show(Request $request): Response
    {
        $id = $request->attribute('id');
        $post = Post::find($id);
        
        if (!$post || $post->status !== 'published') {
            return new Response($this->view->render('errors/404'), 404);
        }
        
        $content = $this->view->render('post/single', [
            'post' => $post,
            'title' => $post->title
        ]);
        
        return new Response($content);
    }
}