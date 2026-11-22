<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /** Render a template within the application layout. */
    public static function render(string $view, array $data = [], int $status = 200): void
    {
        http_response_code($status);
        extract($data, EXTR_SKIP);
        ob_start();
        require dirname(__DIR__, 2) . '/views/' . $view . '.php';
        $content = ob_get_clean();
        require dirname(__DIR__, 2) . '/views/layout.php';
    }
}
