<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Core\Database;

final class UserRepository
{
    /** Find a user by their id. */
    public function find(int $id): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE id=?');
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** Find a user by email. */
    public function findByEmail(string $email): ?array
    {
        $stmt = Database::connection()->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch() ?: null;
    }

    /** Create a student account. */
    public function create(array $user): void
    {
        $sql = 'INSERT INTO users (full_name,email,password_hash,role,student_type) '
            . "VALUES (?, ?, ?, 'student', ?)";
        $stmt = Database::connection()->prepare($sql);
        $stmt->execute([$user['full_name'], $user['email'], $user['password_hash'], $user['student_type']]);
    }
}
