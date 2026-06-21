<?php 

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public static function input(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }
    public static function json(): array
    {
        $raw = file_get_content('php://input') ?: '{}';
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }
}