<?php
namespace OOPress\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $query;
    private array $post;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = $_SERVER['REQUEST_URI'] ?? '/';

        $uri = strtok($uri, '?');    // remove query strings
        $uri = rtrim($uri, '/');      // remove trailing slash except root
        if ($uri === '') $uri = '/';

        $this->uri = $uri;
        $this->query = $_GET;
        $this->post = $_POST;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function query(string $key = null, mixed $default = null): mixed
    {
        return $key ? ($this->query[$key] ?? $default) : $this->query;
    }

    public function post(string $key = null, mixed $default = null): mixed
    {
        return $key ? ($this->post[$key] ?? $default) : $this->post;
    }
}

