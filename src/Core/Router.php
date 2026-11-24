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
        foreach ($this->routes[strtoupper($method)] ?? [] as $pattern => $route) {
            $regex = preg_replace('/\\\\\{[a-zA-Z_][a-zA-Z0-9_]*\\\\\}/', '(\\d+)', preg_quote($pattern, '~'));
            if (preg_match('~^' . $regex . '$~D', $path, $matches)) {
                array_shift($matches);
                $route['params'] = array_map('intval', $matches);
                return $route;
            }
        }
        return null;
    }

    /** Return allowed methods for a URI. */
    public function allowedMethods(string $uri): array
    {
        return array_values(array_filter(array_keys($this->routes), fn ($method) => $this->match($method, $uri)));
    }
}
