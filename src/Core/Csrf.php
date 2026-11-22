<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
    /** Return the current session token. */
    public static function token(): string
    {
        return $_SESSION['_csrf'] ??= bin2hex(random_bytes(32));
    }

    /** Check a submitted token against the session token. */
    public static function verify(mixed $token): bool
    {
        return is_string($token) && isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
    }
}
