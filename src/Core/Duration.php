<?php

declare(strict_types=1);

namespace App\Core;

final class Duration
{
    /** Format a duration in minutes for display. */
    public static function formatMinutes(int $minutes): string
    {
        $hours = intdiv($minutes, 60);
        $rest = $minutes % 60;
        return $hours ? $hours . ' sa' . ($rest ? ' ' . $rest . ' dk' : '') : $rest . ' dk';
    }
}
