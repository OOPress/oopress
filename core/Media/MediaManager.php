<?php

declare(strict_types=1);

namespace OOPress\Media;

use OOPress\Path\PathResolver;
use OOPress\Event\HookDispatcher;
use OOPress\Security\AuthorizationManager;
use OOPress\Security\UserInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * MediaManager — Handles file uploads, storage, and management.
 * 
 * GDPR compliance: All media is stored locally by default. No external
 * services are used unless explicitly configured.
 * 
 * @api
 */
class MediaManager
{
    private const ALLOWED_IMAGES = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    private const ALLOWED_DOCUMENTS = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv'];
    private const ALLOWED_VIDEOS = ['mp4', 'webm', 'ogg'];
    private const ALLOWED_AUDIO = ['mp3', 'wav', 'ogg'];
    
    private array $errors = [];
    private array $config;
    
    public function __construct(
        private readonly PathResolver $pathResolver,
        private readonly HookDispatcher $hookDispatcher,
        private readonly AuthorizationManager $authorization,
        private readonly ImageProcessor $imageProcessor,
        array $config = [],
    ) {
        $this->config = array_merge([
            'max_file_size' => 20 * 1024 * 1024, // 20MB
            'allowed_extensions' => array_merge(
                self::ALLOWED_IMAGES,
                self::ALLOWED_DOCUMENTS,
                self::ALLOWED_VIDEOS,
                self::ALLOWED_AUDIO
            ),
            'image_styles' => [
                'thumbnail' => ['width' => 150, 'height' => 150, 'crop' => true],
                'medium' => ['width' => 300, 'height' => 300, 'crop' => false],
                'large' => ['width' => 800, 'height' => 600, 'crop' => false],
            ],
            'private_files' => false, // Files in private directory require auth
        ], $config);
        
        $this->ensureDirectories();
    }
    
    /**
     * Upload a file.
     */
    public function upload(
        UploadedFile $file,
        string $destination = 'public',
        ?int $userId = null,
        array $metadata = []
    ): ?MediaFile {
        // Validate file
        $errors = $this->validateFile($file);
        if (!empty($errors)) {
            $this->errors = array_merge($this->errors, $errors);
            return null;
        }
        
        // Generate safe filename
        $originalName = $file->getClientOriginalName();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = $this->generateFilename($originalName);
        
        // Determine storage path
        $basePath = $destination === 'private' 
            ? $this->pathResolver->getFilesPath() . '/private'
            : $this->pathResolver->getFilesPath() . '/public';
        
        $relativePath = date('Y/m') . '/' . $filename;
        $fullPath = $basePath . '/' . $relativePath;
        
        // Ensure directory exists
        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        // Move file
        $file->move($dir, $filename);
        
        // Create media record
        $mediaFile = new MediaFile(
            id: null,
            filename: $filename,
            originalName: $originalName,
            path: $relativePath,
            destination: $destination,
            mimeType: $file->getMimeType(),
            size: $file->getSize(),
            extension: $extension,
            userId: $userId,
            metadata: $metadata,
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
        
        // Process image if applicable
        if (in_array($extension, self::ALLOWED_IMAGES)) {
            $this->imageProcessor->processUpload($mediaFile, $fullPath, $this->config['image_styles']);
        }
        
        // Dispatch event
        $event = new Event\MediaUploadEvent($mediaFile);
        $this->hookDispatcher->dispatch($event, 'media.upload');
        
        return $mediaFile;
    }
    
    /**
     * Delete a media file.
     */
    public function delete(MediaFile $media, UserInterface $user): bool
    {
        // Check permissions
        if (!$this->authorization->isGranted($user, 'delete', $media)) {
            $this->errors[] = 'Access denied';
            return false;
        }
        
        // Delete physical files
        $basePath = $media->destination === 'private'
            ? $this->pathResolver->getFilesPath() . '/private'
            : $this->pathResolver->getFilesPath() . '/public';
        
        $fullPath = $basePath . '/' . $media->path;
        
        if (file_exists($fullPath)) {
            unlink($fullPath);
        }
        
        // Delete image styles
        if (in_array($media->extension, self::ALLOWED_IMAGES)) {
            $this->imageProcessor->deleteStyles($media, $basePath);
        }
        
        // Dispatch event
        $event = new Event\MediaDeleteEvent($media);
        $this->hookDispatcher->dispatch($event, 'media.delete');
        
        return true;
    }
    
    /**
     * Get URL for a media file.
     */
    public function getUrl(MediaFile $media, ?string $style = null): string
    {
        if ($media->destination === 'private') {
            // Private files go through a controller for access control
            return '/media/private/' . $media->id;
        }
        
        $baseUrl = '/files/public';
        
        if ($style && in_array($media->extension, self::ALLOWED_IMAGES)) {
            return $baseUrl . '/styles/' . $style . '/' . $media->path;
        }
        
        return $baseUrl . '/' . $media->path;
    }
    
    /**
     * Get physical path for a media file.
     */
    public function getPath(MediaFile $media): string
    {
        $basePath = $media->destination === 'private'
            ? $this->pathResolver->getFilesPath() . '/private'
            : $this->pathResolver->getFilesPath() . '/public';
        
        return $basePath . '/' . $media->path;
    }
    
    /**
     * Validate uploaded file.
     */
    private function validateFile(UploadedFile $file): array
    {
        $errors = [];
        
        // Check size
        if ($file->getSize() > $this->config['max_file_size']) {
            $errors[] = sprintf(
                'File too large. Maximum size: %s',
                $this->formatSize($this->config['max_file_size'])
            );
        }
        
        // Check extension
        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $this->config['allowed_extensions'])) {
            $errors[] = sprintf(
                'File type not allowed. Allowed: %s',
                implode(', ', $this->config['allowed_extensions'])
            );
        }
        
        // Check mime type (basic)
        $allowedMimes = $this->getAllowedMimeTypes();
        if (!in_array($file->getMimeType(), $allowedMimes)) {
            $errors[] = 'File type not allowed based on MIME type';
        }
        
        return $errors;
    }
    
    /**
     * Generate a safe filename.
     */
    private function generateFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        
        // Remove special characters
        $basename = preg_replace('/[^a-zA-Z0-9_-]/', '-', $basename);
        $basename = preg_replace('/-+/', '-', $basename);
        $basename = trim($basename, '-');
        
        // Add timestamp and random string
        $timestamp = date('Ymd_His');
        $random = bin2hex(random_bytes(4));
        
        return sprintf('%s_%s_%s.%s', $basename, $timestamp, $random, $extension);
    }
    
    /**
     * Get allowed MIME types.
     */
    private function getAllowedMimeTypes(): array
    {
        return [
            // Images
            'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml',
            // Documents
            'application/pdf', 'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'text/plain', 'text/csv',
            // Video
            'video/mp4', 'video/webm', 'video/ogg',
            // Audio
            'audio/mpeg', 'audio/wav', 'audio/ogg',
        ];
    }
    
    /**
     * Format file size.
     */
    private function formatSize(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Ensure required directories exist.
     */
    private function ensureDirectories(): void
    {
        $directories = [
            $this->pathResolver->getFilesPath() . '/public',
            $this->pathResolver->getFilesPath() . '/public/styles',
            $this->pathResolver->getFilesPath() . '/private',
        ];
        
        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
    
    /**
     * Get errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}