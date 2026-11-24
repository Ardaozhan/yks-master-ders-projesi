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
            redirect('/');
        }
    }

    private function auth(string $requestUri, string $basePath): void
    {
        if (Auth::user()) {
            return;
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
        $token = $_POST['_csrf'] ?? null;
        if (!Csrf::verify($token)) {
            View::render('errors/403', [], 403);
            exit;
        }
    }

}
