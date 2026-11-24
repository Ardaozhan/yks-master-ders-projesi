<?php

declare(strict_types=1);

namespace App\Core;

final class Middleware
{
    private bool $api = false;
    /** Apply the guards configured for a route. */
    public function handle(array $guards, string $requestUri, string $basePath): void
    {
        $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
        $this->api = str_starts_with($path, rtrim($basePath, '/') . '/api/');
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
        if ($this->api) {
            $this->error(401, 'Giriş gerekli');
        }
        redirect('/giris');
    }

    private function role(string $role): void
    {
        if (!Auth::hasRole($role)) {
            if ($this->api) {
                $this->error(403, 'Yetkiniz yok.');
            }
            View::render('errors/403', [], 403);
            exit;
        }
    }

    private function csrf(): void
    {
        $token = $this->api ? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null) : ($_POST['_csrf'] ?? null);
        if (!Csrf::verify($token)) {
            if ($this->api) {
                $this->error(403, 'Geçersiz güvenlik belirteci.');
            }
            View::render('errors/403', [], 403);
            exit;
        }
    }

    private function error(int $status, string $message): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['status' => 'error', 'data' => null, 'error' => $message, 'errors' => null],
            JSON_UNESCAPED_UNICODE);
        exit;
    }
}
