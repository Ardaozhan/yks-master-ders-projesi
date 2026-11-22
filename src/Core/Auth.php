<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
    /** Return the signed-in user. */
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    /** Check the signed-in user's role. */
    public static function hasRole(string $role): bool
    {
        return (self::user()['role'] ?? null) === $role;
    }

    /** Hash a password. */
    public static function hash(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /** Verify a password against its hash. */
    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    /** Start an authenticated session. */
    public static function login(array $user): void
    {
        session_regenerate_id(true);
        $_SESSION['user'] = ['id' => $user['id'], 'full_name' => $user['full_name'], 'role' => $user['role']];
        unset($_SESSION['attempts'], $_SESSION['locked_until']);
    }

    /** End the authenticated session. */
    public static function logout(): void
    {
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }
}
