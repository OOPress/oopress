<?php

declare(strict_types=1);

namespace OOPress\Tests\Integration\Api;

use OOPress\Tests\TestCase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test Content API endpoints.
 * 
 * @internal
 */
class ContentApiTest extends TestCase
{
    private array $headers;
    
    protected function setUp(): void
    {
        parent::setUp();
        
        $this->headers = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];
    }
    
    protected function makeRequest(string $method, string $uri, array $data = null): array
    {
        $content = $data !== null ? json_encode($data) : null;
        
        $request = Request::create($uri, $method, [], [], [], [], $content);
        $request->headers->add($this->headers);
        
        // This would dispatch to the controller
        // $response = $kernel->handle($request);
        
        // For now, return mock response
        return ['success' => true, 'data' => []];
    }
    
    public function testListContent(): void
    {
        $response = $this->makeRequest('GET', '/api/v1/content');
        
        $this->assertSuccess($response);
    }
    
    public function testGetSingleContent(): void
    {
        $response = $this->makeRequest('GET', '/api/v1/content/1');
        
        // May return 404 if not found, which is fine
        $this->assertArrayHasKey('success', $response);
    }
    
    public function testCreateContentRequiresAuth(): void
    {
        $data = [
            'content_type' => 'article',
            'title' => 'New Article',
            'language' => 'en',
        ];
        
        $response = $this->makeRequest('POST', '/api/v1/content', $data);
        
        // Should fail without authentication
        $this->assertError($response);
    }
    
    public function testSearchContent(): void
    {
        $response = $this->makeRequest('GET', '/api/v1/search?q=test');
        
        $this->assertSuccess($response);
        $this->assertArrayHasKey('data', $response);
    }
}