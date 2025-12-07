<?php
namespace OOPress\Core;

class Router
{
    private array $routes = [];

    /**
     * Register a GET route
     */
    public function get(string $path, callable $handler): void
    {
        $this->routes['GET'][$path] = $handler;
    }

    /**
     * Dispatch the current request
     */
    /*public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        $handler = $this->routes[$method][$uri] ?? null;

        if ($handler) {
            call_user_func($handler);
        } else {
            http_response_code(404);
            echo "404 — Page not found";
        }
    }*/

    public function dispatch(): void
    {
        $request = new Request();
        $response = new Response();

        $method = $request->method();
        $uri = $request->uri();

        $handler = $this->routes[$method][$uri] ?? null;

        if ($handler) {
            call_user_func($handler, $request, $response);
        } else {
            $response->status(404)->text("404 — Page not found");
        }
    }



}
