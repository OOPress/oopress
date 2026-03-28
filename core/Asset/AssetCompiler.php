<?php

declare(strict_types=1);

namespace OOPress\Asset;

use MatthiasMullie\Minify;

/**
 * AssetCompiler — Compiles and minifies assets.
 * 
 * @internal
 */
class AssetCompiler
{
    private array $errors = [];
    
    public function __construct(
        private readonly array $config = [],
    ) {}
    
    /**
     * Compile CSS assets.
     */
    public function compileCss(array $assets, string $outputPath, bool $force = false): CompileResult
    {
        $result = new CompileResult('css');
        
        // Check if compilation is needed
        if (!$force && file_exists($outputPath)) {
            $needsCompile = false;
            $outputMtime = filemtime($outputPath);
            
            foreach ($assets as $asset) {
                if ($asset->compile && file_exists($asset->path) && filemtime($asset->path) > $outputMtime) {
                    $needsCompile = true;
                    break;
                }
            }
            
            if (!$needsCompile) {
                $result->setSkipped('Already up to date');
                return $result;
            }
        }
        
        try {
            $minifier = new Minify\CSS();
            
            foreach ($assets as $asset) {
                if ($asset->type !== 'css') {
                    continue;
                }
                
                if ($asset->compile) {
                    if (!file_exists($asset->path)) {
                        $this->errors[] = sprintf('CSS file not found: %s', $asset->path);
                        continue;
                    }
                    
                    $content = file_get_contents($asset->path);
                    
                    // Process imports and URLs
                    $content = $this->processCssUrls($content, $asset);
                    
                    $minifier->add($content);
                    $result->addFile($asset->path);
                }
            }
            
            $minified = $minifier->minify();
            
            // Ensure directory exists
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            file_put_contents($outputPath, $minified);
            $result->setSuccess($outputPath, filesize($outputPath));
            
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            $result->setFailure($e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Compile JavaScript assets.
     */
    public function compileJs(array $assets, string $outputPath, bool $force = false): CompileResult
    {
        $result = new CompileResult('js');
        
        // Check if compilation is needed
        if (!$force && file_exists($outputPath)) {
            $needsCompile = false;
            $outputMtime = filemtime($outputPath);
            
            foreach ($assets as $asset) {
                if ($asset->compile && file_exists($asset->path) && filemtime($asset->path) > $outputMtime) {
                    $needsCompile = true;
                    break;
                }
            }
            
            if (!$needsCompile) {
                $result->setSkipped('Already up to date');
                return $result;
            }
        }
        
        try {
            $minifier = new Minify\JS();
            
            foreach ($assets as $asset) {
                if ($asset->type !== 'js') {
                    continue;
                }
                
                if ($asset->compile) {
                    if (!file_exists($asset->path)) {
                        $this->errors[] = sprintf('JS file not found: %s', $asset->path);
                        continue;
                    }
                    
                    $minifier->add($asset->path);
                    $result->addFile($asset->path);
                }
            }
            
            $minified = $minifier->minify();
            
            // Ensure directory exists
            $outputDir = dirname($outputPath);
            if (!is_dir($outputDir)) {
                mkdir($outputDir, 0755, true);
            }
            
            file_put_contents($outputPath, $minified);
            $result->setSuccess($outputPath, filesize($outputPath));
            
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            $result->setFailure($e->getMessage());
        }
        
        return $result;
    }
    
    /**
     * Copy a font file.
     */
    public function copyFont(string $sourcePath, string $targetPath, bool $force = false): bool
    {
        if (!$force && file_exists($targetPath)) {
            $sourceMtime = filemtime($sourcePath);
            $targetMtime = filemtime($targetPath);
            
            if ($sourceMtime <= $targetMtime) {
                return true;
            }
        }
        
        $targetDir = dirname($targetPath);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        return copy($sourcePath, $targetPath);
    }
    
    /**
     * Process CSS URLs to ensure they point to the right place.
     */
    private function processCssUrls(string $content, AssetDefinition $asset): string
    {
        // Replace relative URLs with absolute paths
        $assetDir = dirname($asset->path);
        $publicDir = $this->config['public_dir'] ?? '/assets';
        
        $pattern = '/url\([\'"]?([^\'")]+)[\'"]?\)/';
        
        $content = preg_replace_callback($pattern, function($matches) use ($assetDir, $publicDir) {
            $url = $matches[1];
            
            // Skip absolute URLs and data URIs
            if (str_starts_with($url, 'http') || str_starts_with($url, 'data:')) {
                return $matches[0];
            }
            
            // Skip already processed URLs
            if (str_starts_with($url, $publicDir)) {
                return $matches[0];
            }
            
            // Resolve relative path
            $resolvedPath = realpath($assetDir . '/' . $url);
            if ($resolvedPath && file_exists($resolvedPath)) {
                $relativePath = str_replace($this->config['project_root'] ?? '', '', $resolvedPath);
                return sprintf('url("%s")', $relativePath);
            }
            
            return $matches[0];
        }, $content);
        
        return $content;
    }
    
    /**
     * Get errors.
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}