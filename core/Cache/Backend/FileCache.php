<?php

declare(strict_types=1);

namespace OOPress\Cache\Backend;

use OOPress\Cache\CacheInterface;

/**
 * FileCache — File-based cache backend.
 * 
 * @api
 */
class FileCache implements CacheInterface
{
    private string $cachePath;
    private int $directoryLevel;
    
    public function __construct(?string $cachePath = null, int $directoryLevel = 2)
    {
        $this->cachePath = $cachePath ?? sys_get_temp_dir() . '/oopress_cache';
        $this->directoryLevel = max(1, min($directoryLevel, 5));
        
        if (!is_dir($this->cachePath)) {
            mkdir($this->cachePath, 0755, true);
        }
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return $default;
        }
        
        $data = file_get_contents($file);
        
        if ($data === false) {
            return $default;
        }
        
        $cache = unserialize($data);
        
        // Check expiration
        if ($cache['expires'] !== null && $cache['expires'] < time()) {
            $this->delete($key);
            return $default;
        }
        
        return $cache['value'];
    }
    
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $file = $this->getFilePath($key);
        $dir = dirname($file);
        
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        
        $cache = [
            'value' => $value,
            'expires' => $ttl !== null ? time() + $ttl : null,
            'created' => time(),
        ];
        
        $tempFile = $file . '.tmp';
        
        if (file_put_contents($tempFile, serialize($cache)) !== false) {
            return rename($tempFile, $file);
        }
        
        return false;
    }
    
    public function delete(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (file_exists($file)) {
            return unlink($file);
        }
        
        return true;
    }
    
    public function deleteMultiple(array $keys): bool
    {
        $success = true;
        
        foreach ($keys as $key) {
            if (!$this->delete($key)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    public function clear(): bool
    {
        return $this->recursiveDelete($this->cachePath);
    }
    
    public function has(string $key): bool
    {
        $file = $this->getFilePath($key);
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = file_get_contents($file);
        
        if ($data === false) {
            return false;
        }
        
        $cache = unserialize($data);
        
        if ($cache['expires'] !== null && $cache['expires'] < time()) {
            $this->delete($key);
            return false;
        }
        
        return true;
    }
    
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $results = [];
        
        foreach ($keys as $key) {
            $results[$key] = $this->get($key, $default);
        }
        
        return $results;
    }
    
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $success = true;
        
        foreach ($values as $key => $value) {
            if (!$this->set($key, $value, $ttl)) {
                $success = false;
            }
        }
        
        return $success;
    }
    
    public function invalidateTag(string $tag): bool
    {
        // Tags are handled at CacheManager level
        return true;
    }
    
    public function invalidateTags(array $tags): bool
    {
        return true;
    }
    
    /**
     * Get file path for a cache key.
     */
    private function getFilePath(string $key): string
    {
        $hash = md5($key);
        $path = $this->cachePath;
        
        for ($i = 0; $i < $this->directoryLevel; $i++) {
            $path .= '/' . substr($hash, $i * 2, 2);
        }
        
        return $path . '/' . $hash . '.cache';
    }
    
    /**
     * Recursively delete a directory.
     */
    private function recursiveDelete(string $path): bool
    {
        if (!is_dir($path)) {
            return true;
        }
        
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $item) {
            if ($item->isDir()) {
                rmdir($item->getPathname());
            } else {
                unlink($item->getPathname());
            }
        }
        
        return rmdir($path);
    }
}