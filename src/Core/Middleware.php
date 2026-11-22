<?php

declare(strict_types=1);

namespace App\Core;

final class Middleware
{
    /** Apply the guards configured for a route. */
    public function handle(array $guards, string $requestUri, string $basePath): void
    {
        foreach ($guards as $guard) {
            if ($guard === 'guest') {
                $this->guest();
            } elseif ($guard === 'auth') {
                $this->auth($requestUri, $basePath);
            } elseif ($guard === 'csrf') {
                $this->csrf();
            } elseif (str_starts_with($guard, 'role:')) {
                $this->role(substr($guard, 5));
            }
        }
    }

    private function guest(): void
    {
        if (Auth::user()) {
            redirect(Auth::hasRole('admin') ? '/yonetim' : '/bugun');
        }
    }

    private function auth(string $requestUri, string $basePath): void
    {
        if (Auth::user()) {
            return;
        }
        if (str_starts_with(parse_url($requestUri, PHP_URL_PATH), rtrim($basePath, '/') . '/api/')) {
            http_response_code(401);
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['status' => 'error', 'data' => null, 'error' => 'Giriş gerekli'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        redirect('/giris');
    }

    private function role(string $role): void
    {
        if (!Auth::hasRole($role)) {
            View::render('errors/403', [], 403);
            exit;
        }
    }

    private function csrf(): void
    {
        if (!Csrf::verify($_POST['_csrf'] ?? null)) {
            View::render('errors/403', [], 403);
            exit;
        }
    }
}
