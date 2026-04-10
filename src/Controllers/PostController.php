<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Post;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class PostController
{
    private Engine $view;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
    }
    
    public function home(Request $request): Response
    {
        $page = (int)($request->input('page') ?? 1);
        $posts = Post::query()
            ->where(['status' => 'published'])
            ->orderBy('published_at', 'DESC')
            ->paginate(10, $page);
        
        $content = $this->view->render('home', [
            'posts' => $posts['data'],
            'pagination' => [
                'current' => $posts['current_page'],
                'last' => $posts['last_page'],
                'total' => $posts['total']
            ],
            'title' => __('Welcome to OOPress')
        ]);
        
        return new Response($content);
    }
    
    public function show(Request $request): Response
    {
        $slug = $request->attribute('slug');
        $post = Post::firstWhere(['slug' => $slug, 'status' => 'published']);
        
        if (!$post) {
            return new Response($this->view->render('errors/404'), 404);
        }
        
        $post->incrementViews();
        
        $content = $this->view->render('post/single', [
            'post' => $post,
            'title' => $post->title
        ]);
        
        return new Response($content);
    }
}