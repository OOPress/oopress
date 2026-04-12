<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Post;
use OOPress\Models\Setting;
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
        $posts = Post::where(['status' => 'published']);
        
        $content = $this->view->render('home', [
            'posts' => $posts,
            'title' => __('Welcome to OOPress')
        ]);
        
        return new Response($content);
    }
    
    public function show(Request $request): Response
    {
        $slug = $request->attribute('slug');
        $post = Post::firstWhere(['slug' => $slug, 'status' => 'published']);
        
        if (!$post) {
            $content = $this->view->render('errors/404', [
                'title' => 'Page Not Found'
            ]);
            return new Response($content, 404);
        }
        
        $post->incrementViews();
        
        // Get categories and tags
        $categories = $post->getCategories();
        $tags = $post->getTags();
        
        // Get auth instance
        $auth = null;
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['user_id'])) {
            $auth = new \OOPress\Core\Auth(new \OOPress\Core\Session());
        }
        
        $content = $this->view->render('post/single', [
            'title' => $post->title,
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
            'date_format' => Setting::get('date_format', 'F j, Y'),
            'time_format' => Setting::get('time_format', 'g:i a'),
            'auth' => $auth  // Add this line
        ]);
        
        return new Response($content);
    }
}