<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Comment;
use OOPress\Models\Post;
use OOPress\Models\Setting;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class CommentController
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
    
    // Frontend: Submit comment
    public function submit(Request $request): Response
    {
        $enabled = Setting::get('enable_comments', true);
        if (!$enabled) {
            return Response::redirect('/');
        }
        
        if ($request->method() !== 'POST') {
            return Response::redirect('/');
        }
        
        // Get all inputs with null coalescing
        $postId = (int)($request->input('post_id') ?? 0);
        $post = Post::find($postId);
        
        if (!$post) {
            return new Response('Post not found', 404);
        }
        
        // Use null coalescing to handle missing inputs
        $authorName = trim($request->input('author_name') ?? '');
        $authorEmail = trim($request->input('author_email') ?? '');
        $authorUrl = trim($request->input('author_url') ?? '');
        $content = trim($request->input('content') ?? '');
        $parentId = (int)($request->input('parent_id') ?? 0);
        
        // Validation
        $errors = [];
        
        // Check if user is logged in
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $isLoggedIn = isset($_SESSION['user_id']);
        
        if (!$isLoggedIn) {
            if (empty($authorName)) {
                $errors[] = __('Name is required');
            } elseif (strlen($authorName) < 2) {
                $errors[] = __('Name must be at least 2 characters');
            }
            
            if (empty($authorEmail)) {
                $errors[] = __('Email is required');
            } elseif (!filter_var($authorEmail, FILTER_VALIDATE_EMAIL)) {
                $errors[] = __('Invalid email address');
            }
        }
        
        if (empty($content)) {
            $errors[] = __('Comment is required');
        } elseif (strlen($content) < 5) {
            $errors[] = __('Comment must be at least 5 characters');
        } elseif (strlen($content) > 5000) {
            $errors[] = __('Comment is too long (max 5000 characters)');
        }
        
        if (!empty($errors)) {
            $_SESSION['comment_errors'] = $errors;
            $_SESSION['comment_data'] = [
                'author_name' => $authorName,
                'author_email' => $authorEmail,
                'author_url' => $authorUrl,
                'content' => $content
            ];
            return Response::redirect('/post/' . $post->slug . '#comment-form');
        }
        
        // Check if moderation is required
        $moderation = Setting::get('comment_moderation', true);
        $status = $moderation ? 'pending' : 'approved';
        
        // Get user ID if logged in
        $userId = null;
        if ($isLoggedIn) {
            $userId = $_SESSION['user_id'];
            // For logged-in users, use their name and email
            $user = \OOPress\Models\User::find($userId);
            if ($user) {
                $authorName = $user->display_name ?? $user->username;
                $authorEmail = $user->email;
            }
        }
        
        // Save comment
        $comment = new Comment([
            'post_id' => $postId,
            'user_id' => $userId,
            'author_name' => $authorName,
            'author_email' => $authorEmail,
            'author_url' => $authorUrl,
            'author_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'content' => nl2br(htmlspecialchars($content)),
            'status' => $status,
            'parent_id' => $parentId
        ]);
        
        if ($comment->save()) {
            $_SESSION['comment_success'] = $moderation ? 
                __('Your comment has been submitted and awaits moderation.') :
                __('Your comment has been posted successfully.');
        } else {
            $_SESSION['comment_errors'] = [__('Failed to save comment. Please try again.')];
        }
        
        // Clear stored comment data
        unset($_SESSION['comment_data']);
        
        return Response::redirect('/post/' . $post->slug . '#comments');
    }
    
    // Admin: Manage comments
    public function index(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $status = $request->input('status', 'pending');
        $comments = Comment::where(['status' => $status]);
        
        $content = $this->view->render('admin/comments/index', [
            'title' => __('Manage Comments'),
            'comments' => $comments,
            'current_status' => $status,
            'pending_count' => Comment::getPendingCount()
        ]);
        
        return new Response($content);
    }
    
    public function approve(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $comment = Comment::find($id);
        
        if ($comment) {
            $comment->approve();
        }
        
        return Response::redirect('/admin/comments');
    }
    
    public function spam(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $comment = Comment::find($id);
        
        if ($comment) {
            $comment->markAsSpam();
        }
        
        return Response::redirect('/admin/comments');
    }
    
    public function trash(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $comment = Comment::find($id);
        
        if ($comment) {
            $comment->trash();
        }
        
        return Response::redirect('/admin/comments');
    }
    
    public function delete(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $comment = Comment::find($id);
        
        if ($comment) {
            $comment->delete();
        }
        
        return Response::redirect('/admin/comments');
    }
}