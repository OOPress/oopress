<?php

declare(strict_types=1);

namespace OOPress\Http;

class Response
{
    private array $headers = [];
    private string $content;
    private int $statusCode;
    
    public function __construct(string $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }
    
    /**
     * Get all headers
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * Create JSON response
     */
    public static function json(array $data, int $statusCode = 200): self
    {
        $response = new self(json_encode($data), $statusCode);
        $response->header('Content-Type', 'application/json');
        return $response;
    }
    
    /**
     * Create redirect response
     */
    public static function redirect(string $url, int $statusCode = 302): self
    {
        $response = new self('', $statusCode);
        $response->header('Location', $url);
        return $response;
    }
    
    /**
     * Set a header
     */
    public function header(string $key, string $value): self
    {
        $this->headers[$key] = $value;
        return $this;
    }
    
    /**
     * Send response to browser
     */
    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $key => $value) {
            header("{$key}: {$value}");
        }
        
        echo $this->content;
    }
    
    /**
     * Set content
     */
    public function setContent(string $content): self
    {
        $this->content = $content;
        return $this;
    }
    
    /**
     * Get content
     */
    public function getContent(): string
    {
        return $this->content;
    }
    
    /**
     * Get status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }
}