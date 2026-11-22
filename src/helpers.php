<?php

declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function url(string $path): string
{
    return rtrim($GLOBALS['config']['base_path'], '/') . $path;
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function input(string $key): string
{
    return is_string($_POST[$key] ?? null) ? trim($_POST[$key]) : '';
}

function rawInput(string $key): string
{
    return is_string($_POST[$key] ?? null) ? $_POST[$key] : '';
}
