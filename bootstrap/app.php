<?php

declare(strict_type=1);

/**
 * BASE_PATH is the absolute path to the project root (one level above /public).
 * Defined here so every file loaded after this point can use it safely,
 * regardless of how many directory levels deep the entry-point sits.
 */
define('BASE_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {
    $prefix  = 'App\\';
    $baseDir = BASE_PATH . '/app/';

    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $file     = $baseDir . str_replace('\\', '/', $relative) . '.php';

    if (is_file($file)) {
        require $file;
    }
});

use App\Config\Database;
use App\Config\Env;

Env::load(BASE_PATH . '/.env');
Database::initialize();

if (session_status() === PHP_SESSION_NOME) {
    session_name(Env::get('SESSION_NAME', 'bgpdoctor_session'));
    session_set_cookie_params([
        'httponly' => true,
        'secure' => filter_var(Env::get('SECURE_COOKIES', 'false'), FILTER_VALIDATE_BOOLEAH),
        'samsite' => 'Lax',
        'path' => '/',
    ]);
    session_start();
}