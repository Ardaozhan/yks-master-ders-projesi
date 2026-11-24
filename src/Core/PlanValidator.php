<?php

declare(strict_types=1);

namespace App\Core;

use DateTimeImmutable;

final class PlanValidator
{
    /** Validate plan fields. */
    public function plan(array $data): array
    {
        $errors = $this->note($data);
        $date = (string) ($data['date'] ?? '');
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        if (!$parsed || $parsed->format('Y-m-d') !== $date) {
            $errors['date'] = 'Geçerli bir tarih seçin.';
        }
        if (!$this->inRange($data['target_minutes'] ?? '', 5, 600)) {
            $errors['target_minutes'] = 'Hedef süre 5 ile 600 dakika arasında olmalıdır.';
        }
        return $errors;
    }

    /** Validate a status change and its record fields. */
    public function status(array $data): array
    {
        $errors = $this->note($data);
        $status = $data['status'] ?? '';
        $actual = $data['actual_minutes'] ?? '';
        if (!in_array($status, ['planned', 'in_progress', 'completed'], true)) {
            $errors['status'] = 'Geçerli bir durum seçin.';
        }
        if ($status !== 'planned' && ($actual !== '' || $status === 'completed') && !$this->inRange($actual, 1, 600)) {
            $errors['actual_minutes'] = 'Gerçekleşen süre 1 ile 600 dakika arasında olmalıdır.';
        }
        return $errors;
    }

    private function note(array $data): array
    {
        $note = (string) ($data['note'] ?? '');
        $length = function_exists('mb_strlen') ? mb_strlen($note, 'UTF-8') : preg_match_all('/./us', $note);
        return $length > 255 ? ['note' => 'Not en fazla 255 karakter olmalıdır.'] : [];
    }

    private function inRange(mixed $value, int $min, int $max): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => $min, 'max_range' => $max]])
            !== false;
    }
}
