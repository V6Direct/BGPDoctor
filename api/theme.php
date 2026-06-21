<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require __DIR__ . '/../app/Helpers/function.php';

use App\Core\Auth;
use App\Core\Requests;
use App\Core\Response;

if (!Auth::check()) {
    Response::json(['ok' => false, 'message' => 'Unauthorized'], 401);
}

$data = Requests::json();
$theme = in_array($data['theme'] ?? '', ['dark', 'light'], true) ? $data['theme'] : 'dark';
$_SESSION['theme'] = $theme;
Response::json(['ok' => true, 'theme' => $theme]); 