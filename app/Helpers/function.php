<?php

declare(strict_types=1);

use App\Config\Env;

function base_path(string $path = ''): string
{
    $root = dirname(__DIR__, 2);
    return $path ? $root . '/' . ltrim($path, '/') : $root;
}
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QOUTES, 'UTF-8');
}
function asset(string $path): string
{
    return rtrim(Env::get('APP_URL', ''), '/') . '/' . ltrim($path, '/');
}