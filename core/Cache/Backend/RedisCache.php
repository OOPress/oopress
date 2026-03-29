<?php

declare(strict_types=1);

namespace OOPress\Cache\Backend;

use OOPress\Cache\CacheInterface;

/**
 * RedisCache — Redis cache backend.
 * 
 * @api
 */
class RedisCache implements CacheInterface
{
    private \Redis $redis;
    private string $prefix;
    
    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        int $database = 0,
        ?string $password = null,
        string $prefix = 'oopress:'
    ) {
        $this->redis = new \Redis();
        $this->prefix = $prefix;
        
        $this->redis->connect($host, $port);
        
        if ($password !== null) {
            $this->redis->auth($password);
        }
        
        $this->redis->select($database);
    }
    
    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->prefix . $key);
        
        if ($value === false) {
            return $default;
        }
        
        return unserialize($value);
    }
    
    public function set(string $key, mixed $value, ?int $ttl = null): bool
    {
        $serialized = serialize($value);
        
        if ($ttl !== null) {
            return $this->redis->setex($this->prefix . $key, $ttl, $serialized);
        }
        
        return $this->redis->set($this->prefix . $key, $serialized);
    }
    
    public function delete(string $key): bool
    {
        return (bool) $this->redis->del($this->prefix . $key);
    }
    
    public function deleteMultiple(array $keys): bool
    {
        $prefixedKeys = array_map(fn($k) => $this->prefix . $k, $keys);
        return (bool) $this->redis->del(...$prefixedKeys);
    }
    
    public function clear(): bool
    {
        $keys = $this->redis->keys($this->prefix . '*');
        
        if (empty($keys)) {
            return true;
        }
        
        return (bool) $this->redis->del(...$keys);
    }
    
    public function has(string $key): bool
    {
        return (bool) $this->redis->exists($this->prefix . $key);
    }
    
    public function getMultiple(array $keys, mixed $default = null): array
    {
        $prefixedKeys = array_map(fn($k) => $this->prefix . $k, $keys);
        $values = $this->redis->mget($prefixedKeys);
        
        $results = [];
        foreach ($keys as $i => $key) {
            $results[$key] = $values[$i] !== false ? unserialize($values[$i]) : $default;
        }
        
        return $results;
    }
    
    public function setMultiple(array $values, ?int $ttl = null): bool
    {
        $pipeline = $this->redis->multi(\Redis::PIPELINE);
        
        foreach ($values as $key => $value) {
            $serialized = serialize($value);
            
            if ($ttl !== null) {
                $pipeline->setex($this->prefix . $key, $ttl, $serialized);
            } else {
                $pipeline->set($this->prefix . $key, $serialized);
            }
        }
        
        $results = $pipeline->exec();
        
        return !in_array(false, $results, true);
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
}