<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;

final class Auth 
{
    public static function attempt(string $email, string $password): bool
    {
        $user = (new User())->findByEmail($email);
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return false;
        }

        $_SESSION['user'] = [
            'id' => $user['id'],
            'name' => $user['name'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];
        session_regenerate_id(true);
        return true;
    }
    public static function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }
    public static function check(): bool
    {
        return isset($_SESSION['user']);
    }
    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cooki_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}