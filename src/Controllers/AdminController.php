<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Post;
use OOPress\Models\User;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;
use OOPress\Models\Term;
use OOPress\Models\Taxonomy;
use OOPress\Core\SEO;

class AdminController
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
    
    private function denyAccess(): Response
    {
        return new Response('Access denied. Admin privileges required.', 403);
    }
    
    public function dashboard(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return $this->denyAccess();
        }
        
        // Get statistics
        $totalPosts = count(Post::all());
        $publishedPosts = count(Post::where(['status' => 'published']));
        $draftPosts = count(Post::where(['status' => 'draft']));
        $totalUsers = count(User::all());
        
        $content = $this->view->render('admin/dashboard', [
            'title' => __('Admin Dashboard'),
            'stats' => [
                'total_posts' => $totalPosts,
                'published_posts' => $publishedPosts,
                'draft_posts' => $draftPosts,
                'total_users' => $totalUsers
            ],
            'recent_posts' => Post::query()->orderBy('created_at', 'DESC')->limit(5)->get(),
            'recent_users' => User::query()->orderBy('created_at', 'DESC')->limit(5)->get()
        ]);
        
        return new Response($content);
    }
    
    public function posts(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return $this->denyAccess();
        }
        
        $posts = Post::query()->orderBy('created_at', 'DESC')->get();
        
        $content = $this->view->render('admin/posts/index', [
            'title' => __('Manage Posts'),
            'posts' => $posts
        ]);
        
        return new Response($content);
    }
    
    public function createPost(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return $this->denyAccess();
        }
        
        if ($request->method() === 'POST') {
            $title = $request->input('title');
            $slug = $this->createSlug($title);
            $content = $request->input('content');
            $excerpt = $request->input('excerpt');
            $status = $request->input('status', 'draft');
            $categories = $request->input('categories', []);
            $tags = $request->input('tags', []);
            $metaTitle = $request->input('meta_title');
            $metaDescription = $request->input('meta_description');
            $metaKeywords = $request->input('meta_keywords');
            $canonicalUrl = $request->input('canonical_url');
            $ogTitle = $request->input('og_title');
            $ogDescription = $request->input('og_description');
            $ogImage = $request->input('og_image');
            $schemaType = $request->input('schema_type');
            
            $post = new Post([
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'excerpt' => $excerpt,
                'status' => $status,
                'type' => 'post',
                'author_id' => $_SESSION['user_id'],
                'meta_title' => $metaTitle,
                'meta_description' => $metaDescription,
                'meta_keywords' => $metaKeywords,
                'canonical_url' => $canonicalUrl,
                'og_title' => $ogTitle,
                'og_description' => $ogDescription,
                'og_image' => $ogImage,
                'schema_type' => $schemaType
            ]);
            
            if ($post->save()) {
                // Save categories
                if (!empty($categories)) {
                    $post->setCategories($categories);
                }
                
                // Save tags
                if (!empty($tags)) {
                    $post->setTags($tags);
                }
                
                return Response::redirect('/admin/posts');
            }
            
            $error = __('Failed to create post');
        }
        
        $content = $this->view->render('admin/posts/create', [
            'title' => __('Create New Post'),
            'error' => $error ?? null
        ]);
        
        return new Response($content);
    }

    public function editPost(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return $this->denyAccess();
        }
        
        $id = (int)$request->attribute('id');
        $post = Post::find($id);
        
        if (!$post) {
            return new Response('Post not found', 404);
        }
        
        if ($request->method() === 'POST') {
            $post->title = $request->input('title');
            $post->content = $request->input('content');
            $post->excerpt = $request->input('excerpt');
            $post->status = $request->input('status', 'draft');
            
            $categories = $request->input('categories', []);
            $tags = $request->input('tags', []);
            
            if ($post->save()) {
                // Save categories
                $post->setCategories($categories);
                
                // Save tags
                $post->setTags($tags);
                
                return Response::redirect('/admin/posts');
            }
            
            $error = __('Failed to update post');
        }
        
        $content = $this->view->render('admin/posts/edit', [
            'title' => __('Edit Post'),
            'post' => $post,
            'error' => $error ?? null
        ]);
        
        return new Response($content);
    }
    
    public function deletePost(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return $this->denyAccess();
        }
        
        $id = (int)$request->attribute('id');
        $post = Post::find($id);
        
        if ($post) {
            $post->delete();
        }
        
        return Response::redirect('/admin/posts');
    }
    
    public function users(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return $this->denyAccess();
        }
        
        $users = User::query()->orderBy('created_at', 'DESC')->get();
        
        $content = $this->view->render('admin/users/index', [
            'title' => __('Manage Users'),
            'users' => $users
        ]);
        
        return new Response($content);
    }
    
    public function editUser(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return $this->denyAccess();
        }
        
        $id = (int)$request->attribute('id');
        $user = User::find($id);
        
        if (!$user) {
            return new Response('User not found', 404);
        }
        
        if ($request->method() === 'POST') {
            $user->display_name = $request->input('display_name');
            $user->email = $request->input('email');
            $user->role = $request->input('role');
            $user->status = $request->input('status');
            
            $password = $request->input('password');
            if (!empty($password)) {
                $user->password = password_hash($password, PASSWORD_DEFAULT);
            }
            
            if ($user->save()) {
                return Response::redirect('/admin/users');
            }
            
            $error = __('Failed to update user');
        }
        
        $content = $this->view->render('admin/users/edit', [
            'title' => __('Edit User'),
            'user' => $user,
            'error' => $error ?? null
        ]);
        
        return new Response($content);
    }
    
    private function createSlug(string $title): string
    {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[^a-z0-9-]/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        $original = $slug;
        $counter = 1;
        while (Post::firstWhere(['slug' => $slug])) {
            $slug = $original . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }
}