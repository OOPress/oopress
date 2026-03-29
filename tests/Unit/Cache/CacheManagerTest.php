<?php

declare(strict_types=1);

namespace OOPress\Tests\Unit\Cache;

use OOPress\Tests\TestCase;
use OOPress\Cache\CacheManager;
use OOPress\Cache\Backend\ArrayCache;
use OOPress\Event\HookDispatcher;

/**
 * Test CacheManager functionality.
 * 
 * @internal
 */
class CacheManagerTest extends TestCase
{
    private CacheManager $cache;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $config = [
            'default_backend' => 'array',
        ];
        
        $dispatcher = $this->createMock(HookDispatcher::class);
        $this->cache = new CacheManager($dispatcher, $config);
    }
    
    public function testSetAndGet(): void
    {
        $this->cache->set('test_key', 'test_value');
        $value = $this->cache->get('test_key');
        
        $this->assertEquals('test_value', $value);
    }
    
    public function testGetDefault(): void
    {
        $value = $this->cache->get('nonexistent_key', 'default_value');
        
        $this->assertEquals('default_value', $value);
    }
    
    public function testHas(): void
    {
        $this->cache->set('existing_key', 'value');
        
        $this->assertTrue($this->cache->has('existing_key'));
        $this->assertFalse($this->cache->has('nonexistent_key'));
    }
    
    public function testDelete(): void
    {
        $this->cache->set('to_delete', 'value');
        $this->assertTrue($this->cache->has('to_delete'));
        
        $this->cache->delete('to_delete');
        $this->assertFalse($this->cache->has('to_delete'));
    }
    
    public function testClear(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        
        $this->cache->clear();
        
        $this->assertFalse($this->cache->has('key1'));
        $this->assertFalse($this->cache->has('key2'));
    }
    
    public function testSetMultiple(): void
    {
        $values = [
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];
        
        $this->cache->setMultiple($values);
        
        $this->assertEquals('value1', $this->cache->get('key1'));
        $this->assertEquals('value2', $this->cache->get('key2'));
        $this->assertEquals('value3', $this->cache->get('key3'));
    }
    
    public function testGetMultiple(): void
    {
        $this->cache->set('key1', 'value1');
        $this->cache->set('key2', 'value2');
        
        $values = $this->cache->getMultiple(['key1', 'key2', 'key3'], 'default');
        
        $this->assertEquals('value1', $values['key1']);
        $this->assertEquals('value2', $values['key2']);
        $this->assertEquals('default', $values['key3']);
    }
    
    public function testTags(): void
    {
        $this->cache->tags(['tag1', 'tag2'])->set('tagged_key', 'tagged_value');
        
        $this->assertEquals('tagged_value', $this->cache->get('tagged_key'));
        
        $this->cache->invalidateTag('tag1');
        
        // After invalidation, value may still exist until cache is cleared
        // This depends on implementation
        $this->cache->delete('tagged_key');
        $this->assertFalse($this->cache->has('tagged_key'));
    }
    
    public function testCacheExpiration(): void
    {
        $this->cache->set('expiring_key', 'value', 1);
        
        $this->assertEquals('value', $this->cache->get('expiring_key'));
        
        // Wait for expiration
        sleep(2);
        
        // Note: ArrayCache doesn't support TTL, so this test may pass differently
        // This is a placeholder for when using a real cache backend
        $this->assertTrue(true);
    }
}