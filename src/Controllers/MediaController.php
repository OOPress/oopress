<?php

declare(strict_types=1);

namespace OOPress\Controllers;

use OOPress\Models\Media;
use OOPress\Http\Request;
use OOPress\Http\Response;
use League\Plates\Engine;

class MediaController
{
    private Engine $view;
    private string $uploadPath;
    private array $allowedTypes = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp',
        'application/pdf', 'text/plain', 'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
    ];
    private int $maxFileSize = 5242880; // 5MB
    
    public function __construct()
    {
        $this->view = new Engine(__DIR__ . '/../../views');
        $this->uploadPath = __DIR__ . '/../../storage/uploads/';
        
        // Create upload directory if not exists
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }
    }
    
    private function checkAdminAccess(): bool
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
    }
    
    public function index(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $media = Media::query()->orderBy('created_at', 'DESC')->get();
        
        $content = $this->view->render('admin/media/index', [
            'title' => __('Media Library'),
            'media' => $media,
            'error' => null
        ]);
        
        return new Response($content);
    }
    
    public function upload(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $media = Media::query()->orderBy('created_at', 'DESC')->get();
            $content = $this->view->render('admin/media/index', [
                'title' => __('Media Library'),
                'media' => $media,
                'error' => __('File upload failed')
            ]);
            return new Response($content, 400);
        }
        
        $file = $_FILES['file'];
        
        // Validate file type
        if (!in_array($file['type'], $this->allowedTypes)) {
            $media = Media::query()->orderBy('created_at', 'DESC')->get();
            $content = $this->view->render('admin/media/index', [
                'title' => __('Media Library'),
                'media' => $media,
                'error' => __('File type not allowed')
            ]);
            return new Response($content, 400);
        }
        
        // Validate file size
        if ($file['size'] > $this->maxFileSize) {
            $media = Media::query()->orderBy('created_at', 'DESC')->get();
            $content = $this->view->render('admin/media/index', [
                'title' => __('Media Library'),
                'media' => $media,
                'error' => __('File too large. Max 5MB')
            ]);
            return new Response($content, 400);
        }
        
        // Generate safe filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = uniqid() . '.' . $extension;
        $destination = $this->uploadPath . $filename;
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $destination)) {
            $media = Media::query()->orderBy('created_at', 'DESC')->get();
            $content = $this->view->render('admin/media/index', [
                'title' => __('Media Library'),
                'media' => $media,
                'error' => __('Failed to save file')
            ]);
            return new Response($content, 500);
        }
        
        // Get image dimensions if it's an image
        $width = null;
        $height = null;
        if (strpos($file['type'], 'image/') === 0) {
            $imageInfo = getimagesize($destination);
            if ($imageInfo) {
                $width = $imageInfo[0];
                $height = $imageInfo[1];
            }
        }
        
        // Save to database
        $media = new Media([
            'filename' => $filename,
            'original_name' => $file['name'],
            'path' => $destination,
            'url' => '/storage/' . $filename,
            'mime_type' => $file['type'],
            'size' => $file['size'],
            'width' => $width,
            'height' => $height,
            'author_id' => $_SESSION['user_id']
        ]);
        
        if ($media->save()) {
            return Response::redirect('/admin/media');
        }
        
        // Clean up file if database save fails
        unlink($destination);
        
        $media = Media::query()->orderBy('created_at', 'DESC')->get();
        $content = $this->view->render('admin/media/index', [
            'title' => __('Media Library'),
            'media' => $media,
            'error' => __('Failed to save to database')
        ]);
        
        return new Response($content, 500);
    }
    
    public function delete(Request $request): Response
    {
        if (!$this->checkAdminAccess()) {
            return new Response('Access denied', 403);
        }
        
        $id = (int)$request->attribute('id');
        $media = Media::find($id);
        
        if ($media) {
            // Delete file from disk
            if (file_exists($media->path)) {
                unlink($media->path);
            }
            
            // Delete from database
            $media->delete();
        }
        
        return Response::redirect('/admin/media');
    }
}