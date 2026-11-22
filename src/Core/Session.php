<?php

declare(strict_types=1);

namespace App\Core;

final class Session
{
    /** Start the session with cookie protections. */
    public static function start(): void
    {
        session_set_cookie_params([
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        ]);
        ini_set('session.use_strict_mode', '1');
        session_start();
    }

    /** Store a flash value. */
    public static function flash(string $key, mixed $value): void
    {
        $_SESSION['_flash'][$key] = $value;
    }

    /** Consume a flash value. */
    public static function pull(string $key, mixed $default = null): mixed
    {
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }
}
