<?php

declare(strict_types=1);

namespace OOPress\Http;

class Request
{
    private array $attributes = [];
    
    public function __construct(
        private array $get = [],
        private array $post = [],
        private array $server = [],
        private array $files = [],
        private array $cookies = []
    ) {
    }
    
    /**
     * Create from PHP globals
     */
    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_SERVER, $_FILES, $_COOKIE);
    }
    
    /**
     * Get HTTP method
     */
    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }
    
    /**
     * Get request path
     */
    public function path(): string
    {
        $path = parse_url($this->server['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        return $path === null ? '/' : $path;
    }
    
    /**
     * Get input value from GET or POST
     */
    public function input(string $key, $default = null): mixed
    {
        return $this->post[$key] ?? $this->get[$key] ?? $default;
    }
    
    /**
     * Get all input
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }
    
    /**
     * Check if request expects JSON
     */
    public function expectsJson(): bool
    {
        $accept = $this->server['HTTP_ACCEPT'] ?? '';
        return str_contains($accept, 'application/json');
    }
    
    /**
     * Get JSON input as array
     */
    public function json(): array
    {
        $input = file_get_contents('php://input');
        return json_decode($input, true) ?? [];
    }
    
    /**
     * Get header value
     */
    public function header(string $key, ?string $default = null): ?string
    {
        $headerKey = 'HTTP_' . strtoupper(str_replace('-', '_', $key));
        return $this->server[$headerKey] ?? $default;
    }
    
    /**
     * Get bearer token from Authorization header
     */
    public function bearerToken(): ?string
    {
        $header = $this->header('Authorization');
        if ($header && preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Get route attribute
     */
    public function attribute(string $key, $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }
    
    /**
     * Set route attribute
     */
    public function withAttribute(string $key, $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;
        return $clone;
    }
    
    /**
     * Check if request is AJAX
     */
    public function ajax(): bool
    {
        return $this->header('X-Requested-With') === 'XMLHttpRequest';
    }
    
    /**
     * Get IP address
     */
    public function ip(): ?string
    {
        return $this->server['REMOTE_ADDR'] ?? null;
    }
}