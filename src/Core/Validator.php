<?php

declare(strict_types=1);

namespace App\Core;

final class Validator
{
    /** Validate registration fields. */
    public function register(array $input): array
    {
        $errors = [];
        $name = trim((string) ($input['full_name'] ?? ''));
        $length = function_exists('mb_strlen') ? mb_strlen($name, 'UTF-8') : preg_match_all('/./us', $name);
        if ($length < 2 || $length > 100) {
            $errors['full_name'] = 'Ad soyad 2-100 karakter olmalıdır.';
        }
        if (!filter_var($input['email'] ?? '', FILTER_VALIDATE_EMAIL) || strlen((string) ($input['email'] ?? '')) > 254) {
            $errors['email'] = 'Geçerli bir e-posta girin.';
        }
        $password = (string) ($input['password'] ?? '');
        $passwordLength = function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : preg_match_all('/./us', $password);
        if ($passwordLength < 8) {
            $errors['password'] = 'Parola en az 8 karakter olmalıdır.';
        } elseif (strlen($password) > 72) {
            $errors['password'] = 'Parola en fazla 72 bayt olmalıdır.';
        }
        if (!in_array($input['student_type'] ?? '', ['grade12', 'graduate'], true)) {
            $errors['student_type'] = 'Öğrenci durumunu seçin.';
        }
        return $errors;
    }
}
