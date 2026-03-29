<?php

declare(strict_types=1);

namespace OOPress\Cache\Backend;

use OOPress\Cache\CacheInterface;

/**
 * MemcachedCache — Memcached cache backend.
 * 
 * @api
 */
class MemcachedCache implements CacheInterface
{
    private \Memcached $memcached;
    private string $prefix;
    
    /**
     * @param array<array{0: string, 1: int}> $servers Array of [host, port] pairs
     */
    public function __construct(array $servers = [['127.0.0.1', 11211]], string $prefix = 'oopress:')
    {
        $this->memcached = new \Memcached();
        $this->prefix = $prefix;
        
        // Add servers if not already added
        if (!count($this->memcached->getServerList())) {
            foreach ($servers as $server) {
                $this->memcached->addServer($server[0], $server[1]);
            }
        }
        
        // Set consistent hashing for better distribution
        $this->memcached->setOption(\Memcached::OPT_DISTRIBUTION, \Memcached::DISTRIBUTION_CONSISTENT);
        
        // Enable compression for larger values
        $this->memcached->setOption(\Memcached::OPT_COMPRESSION, true);
        
        // Set TCP no delay for better performance
        $this->memcached->setOption(\Memcached::OPT_TCP_NODELAY, true);
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->memcached->get($this->prefix . $key);
        
        // Check for not found (Memcached returns false for missing keys)
        if ($this->memcached->getResultCode() === \Memcached::RES_NOTFOUND) {
            return $default;
        }
        
        return $value !== false ? $value : $default;
    }
    
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $expiration = $ttl !== null ? $ttl : 0;
        
        return $this->memcached->set($this->prefix . $key, $value, $expiration);
    }
    
    public function delete(string $key): bool
    {
        return $this->memcached->delete($this->prefix . $key);
    }
    
    public function deleteMultiple(array $keys): bool
    {
        $prefixedKeys = array_map(fn($k) => $this->prefix . $k, $keys);
        
        // Memcached::deleteMulti returns array of results
        $results = $this->memcached->deleteMulti($prefixedKeys);
        
        // Check if all deletions succeeded
        foreach ($results as $result) {
            if ($result !== true && $result !== \Memcached::RES_NOTFOUND) {
                return false;
            }
        }
        
        return true;
    }
    
    public function clear(): bool
    {
        return $this->memcached->flush();
    }
    
    public function has(string $key): bool
    {
        $this->memcached->get($this->prefix . $key);
        return $this->memcached->getResultCode() !== \Memcached::RES_NOTFOUND;
    }
    
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $prefixedKeys = array_map(fn($k) => $this->prefix . $k, $keys);
        $values = $this->memcached->getMulti($prefixedKeys);
        
        $results = [];
        foreach ($keys as $key) {
            $prefixed = $this->prefix . $key;
            $results[$key] = $values[$prefixed] ?? $default;
        }
        
        return $results;
    }
    
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $prefixed = [];
        foreach ($values as $key => $value) {
            $prefixed[$this->prefix . $key] = $value;
        }
        
        $expiration = $ttl !== null ? $ttl : 0;
        
        return $this->memcached->setMulti($prefixed, $expiration);
    }
    
    public function invalidateTag(string $tag): bool
    {
        // Tags are handled at CacheManager level
        // Memcached doesn't have native tag support
        return true;
    }
    
    public function invalidateTags(array $tags): bool
    {
        return true;
    }
    
    /**
     * Get server statistics.
     */
    public function getStats(): array
    {
        $stats = [];
        $servers = $this->memcached->getServerList();
        
        foreach ($servers as $server) {
            $serverStats = $this->memcached->getStats($server['host'] . ':' . $server['port']);
            $stats[] = [
                'host' => $server['host'],
                'port' => $server['port'],
                'stats' => $serverStats,
            ];
        }
        
        return $stats;
    }
    
    /**
     * Check if Memcached extension is available.
     */
    public static function isAvailable(): bool
    {
        return extension_loaded('memcached');
    }
}