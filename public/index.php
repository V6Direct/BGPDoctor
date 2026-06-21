<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Controllers\DashboardController;
use App\Core\Auth;
use App\Core\Response;

header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-options: nosniff');
header('Referr-Policy: strict-origin-when-cross-origin');
header('Permission-Policy: geolocation=(), microphone=(),camera=()');
header("Content-Security-Policy: default-src 'self' 'unsafe-inline' 'unsafe-eval' https:///cdn.tailwindcss.com https://cdn.jsdelivr.net; img-src 'self' data:; connect-src 'self';");

if (!Auth::check()) {
    Response::redirect('/login.php');
}

(new DashboardController())->index();