<?php

declare(strict_types=1);

namespace OOPress\Media;

/**
 * MediaFile — Media entity.
 * 
 * @api
 */
class MediaFile
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $filename,
        public readonly string $originalName,
        public readonly string $path,
        public readonly string $destination, // 'public' or 'private'
        public readonly string $mimeType,
        public readonly int $size,
        public readonly string $extension,
        public readonly ?int $userId,
        public readonly array $metadata,
        public readonly \DateTimeImmutable $createdAt,
        public readonly \DateTimeImmutable $updatedAt,
    ) {}
    
    /**
     * Check if file is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }
    
    /**
     * Check if file is a video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mimeType, 'video/');
    }
    
    /**
     * Check if file is audio.
     */
    public function isAudio(): bool
    {
        return str_starts_with($this->mimeType, 'audio/');
    }
    
    /**
     * Check if file is a document.
     */
    public function isDocument(): bool
    {
        return in_array($this->extension, ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt', 'csv']);
    }
    
    /**
     * Get human-readable file size.
     */
    public function getFormattedSize(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
    
    /**
     * Get CSS class for file type icon.
     */
    public function getIconClass(): string
    {
        if ($this->isImage()) {
            return 'fa-file-image';
        }
        
        if ($this->isVideo()) {
            return 'fa-file-video';
        }
        
        if ($this->isAudio()) {
            return 'fa-file-audio';
        }
        
        return match($this->extension) {
            'pdf' => 'fa-file-pdf',
            'doc', 'docx' => 'fa-file-word',
            'xls', 'xlsx' => 'fa-file-excel',
            'txt' => 'fa-file-alt',
            default => 'fa-file',
        };
    }
}