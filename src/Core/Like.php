<?php

declare(strict_types=1);

namespace App\Core;

final class Like
{
    /** Escape MySQL LIKE wildcard and escape characters. */
    public static function escape(string $value): string
    {
        return strtr($value, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']);
    }
}
