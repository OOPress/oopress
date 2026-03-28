<?php

declare(strict_types=1);

namespace OOPress\Media;

use Intervention\Image\ImageManager;
use OOPress\Path\PathResolver;

/**
 * ImageProcessor — Handles image manipulation.
 * 
 * @internal
 */
class ImageProcessor
{
    private ImageManager $imageManager;
    private array $errors = [];
    
    public function __construct(
        private readonly PathResolver $pathResolver,
    ) {
        $this->imageManager = new ImageManager(['driver' => 'gd']);
    }
    
    /**
     * Process uploaded image.
     */
    public function processUpload(MediaFile $media, string $sourcePath, array $styles): void
    {
        if (!$media->isImage()) {
            return;
        }
        
        // Generate image styles
        foreach ($styles as $styleName => $styleConfig) {
            $this->generateStyle($media, $sourcePath, $styleName, $styleConfig);
        }
    }
    
    /**
     * Generate an image style.
     */
    public function generateStyle(MediaFile $media, string $sourcePath, string $styleName, array $config): bool
    {
        try {
            $image = $this->imageManager->make($sourcePath);
            
            $width = $config['width'] ?? null;
            $height = $config['height'] ?? null;
            $crop = $config['crop'] ?? false;
            $quality = $config['quality'] ?? 90;
            
            if ($width || $height) {
                if ($crop && $width && $height) {
                    $image->fit($width, $height);
                } elseif ($width && $height) {
                    $image->resize($width, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } elseif ($width) {
                    $image->resize($width, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                } elseif ($height) {
                    $image->resize(null, $height, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                }
            }
            
            // Save styled image
            $basePath = $media->destination === 'private'
                ? $this->pathResolver->getFilesPath() . '/private'
                : $this->pathResolver->getFilesPath() . '/public';
            
            $stylePath = $basePath . '/styles/' . $styleName . '/' . $media->path;
            $styleDir = dirname($stylePath);
            
            if (!is_dir($styleDir)) {
                mkdir($styleDir, 0755, true);
            }
            
            $image->save($stylePath, $quality);
            
            return true;
            
        } catch (\Exception $e) {
            $this->errors[] = sprintf(
                'Failed to generate style %s for %s: %s',
                $styleName,
                $media->filename,
                $e->getMessage()
            );
            return false;
        }
    }
    
    /**
     * Delete all styles for an image.
     */
    public function deleteStyles(MediaFile $media, string $basePath): void
    {
        $stylesPath = $basePath . '/styles';
        
        if (!is_dir($stylesPath)) {
            return;
        }
        
        $iterator = new \DirectoryIterator($stylesPath);
        
        foreach ($iterator as $style) {
            if ($style->isDot() || !$style->isDir()) {
                continue;
            }
            
            $styleFile = $style->getPathname() . '/' . $media->path;
            if (file_exists($styleFile)) {
                unlink($styleFile);
            }
        }
    }
    
    /**
     * Get image dimensions.
     */
    public function getDimensions(string $path): ?array
    {
        try {
            $image = $this->imageManager->make($path);
            return [
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Exception $e) {
            return null;
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