<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Request;
use App\Core\Response;

if (!Auth::check()) {
    Response::json(['ok' => false, 'message' => 'Unauthorized'], 401);
}

$data  = Request::json();
$theme = in_array($data['theme'] ?? '', ['dark', 'light'], true) ? $data['theme'] : 'dark';
$_SESSION['theme'] = $theme;
Response::json(['ok' => true, 'theme' => $theme]);
