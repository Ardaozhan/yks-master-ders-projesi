<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    /** Set the application base path. */
    public function __construct(private string $basePath = '')
    {
    }

    /** Register a route with its guards. */
    public function add(string $method, string $path, array $handler, array $middleware = []): void
    {
        $this->routes[$method][$path] = ['handler' => $handler, 'middleware' => $middleware];
    }
    /** Find a route for a method and URI. */
    public function match(string $method, string $uri): ?array
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $base = rtrim($this->basePath, '/');
        if ($base !== '') {
            if ($path !== $base && !str_starts_with($path, $base . '/')) {
                return null;
            }
            $path = substr($path, strlen($base)) ?: '/';
        }
        return $this->routes[strtoupper($method)][$path] ?? null;
    }
}
