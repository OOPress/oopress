<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Post;
use OOPress\Models\Setting;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;
use OOPress\Core\SEO;

class HomeController
{
    private Engine $view;
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
    }
    
    public function index(Request $request): Response
    {
        // Get settings
        $postsPerPage = (int)Setting::get('posts_per_page', 10);
        $showExcerpt = (bool)Setting::get('show_excerpt', true);
        $excerptLength = (int)Setting::get('excerpt_length', 55);
        
        // Get page parameter
        $page = (int)($request->input('page') ?? 1);
        $offset = ($page - 1) * $postsPerPage;
        
        // Get published posts
        $allPosts = Post::where(['status' => 'published']);
        $totalPosts = count($allPosts);
        $posts = array_slice($allPosts, $offset, $postsPerPage);

        $seo = new SEO();
        $seo->setHomepage();
        
        $content = $this->view->render('home', [
            'title' => Setting::get('site_title', 'OOPress'),
            'tagline' => Setting::get('site_tagline', 'A modern PHP CMS'),
            'posts' => $posts,
            'show_excerpt' => $showExcerpt,
            'excerpt_length' => $excerptLength,
            'current_page' => $page,
            'total_pages' => ceil($totalPosts / $postsPerPage),
            'date_format' => Setting::get('date_format', 'F j, Y'),
            'time_format' => Setting::get('time_format', 'g:i a'),
            'seo' => $seo
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
        
        // Increment view count
        $post->incrementViews();
        
        // Get post categories and tags
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
        
        $seo = new SEO();
        $seo->setPost($post);

        $content = $this->view->render('post/single', [
            'title' => $post->title,
            'post' => $post,
            'categories' => $categories,
            'tags' => $tags,
            'date_format' => Setting::get('date_format', 'F j, Y'),
            'time_format' => Setting::get('time_format', 'g:i a'),
            'auth' => $auth,
            'seo' => $seo  
        ]);
        
        return new Response($content);
    }
    
    public function category(Request $request): Response
    {
        $slug = $request->attribute('slug');
        
        // Find category by slug
        $categoryTaxonomy = \OOPress\Models\Taxonomy::firstWhere(['slug' => 'category']);
        if (!$categoryTaxonomy) {
            $content = $this->view->render('errors/404', ['title' => 'Category Not Found']);
            return new Response($content, 404);
        }
        
        $category = \OOPress\Models\Term::firstWhere([
            'slug' => $slug,
            'taxonomy_id' => $categoryTaxonomy->id
        ]);
        
        if (!$category) {
            $content = $this->view->render('errors/404', ['title' => 'Category Not Found']);
            return new Response($content, 404);
        }
        
        // Get posts in this category
        $db = \OOPress\Models\Post::getDB();
        $postIds = $db->select('term_relationships', 'object_id', [
            'term_id' => $category->id
        ]);
        
        if (empty($postIds)) {
            $posts = [];
        } else {
            $posts = \OOPress\Models\Post::where([
                'id' => $postIds,
                'status' => 'published'
            ]);
        }
        
        $seo = new SEO();
        $seo->setArchive($category->name, $category->description);

        $content = $this->view->render('archive/category', [
            'title' => __('Category') . ': ' . $category->name,
            'category' => $category,
            'posts' => $posts,
            'date_format' => Setting::get('date_format', 'F j, Y'),
            'seo' => $seo 
        ]);
        
        return new Response($content);
    }
    
    public function tag(Request $request): Response
    {
        $slug = $request->attribute('slug');
        
        // Find tag by slug
        $tagTaxonomy = \OOPress\Models\Taxonomy::firstWhere(['slug' => 'tag']);
        if (!$tagTaxonomy) {
            $content = $this->view->render('errors/404', ['title' => 'Tag Not Found']);
            return new Response($content, 404);
        }
        
        $tag = \OOPress\Models\Term::firstWhere([
            'slug' => $slug,
            'taxonomy_id' => $tagTaxonomy->id
        ]);
        
        if (!$tag) {
            $content = $this->view->render('errors/404', ['title' => 'Tag Not Found']);
            return new Response($content, 404);
        }
        
        // Get posts with this tag
        $db = \OOPress\Models\Post::getDB();
        $postIds = $db->select('term_relationships', 'object_id', [
            'term_id' => $tag->id
        ]);
        
        if (empty($postIds)) {
            $posts = [];
        } else {
            $posts = \OOPress\Models\Post::where([
                'id' => $postIds,
                'status' => 'published'
            ]);
        }
        
        $seo = new SEO();
        $seo->setArchive($tag->name, $tag->description);

        $content = $this->view->render('archive/tag', [
            'title' => __('Tag') . ': ' . $tag->name,
            'tag' => $tag,
            'posts' => $posts,
            'date_format' => Setting::get('date_format', 'F j, Y'),
            'seo' => $seo
        ]);
        
        return new Response($content);
    }
}