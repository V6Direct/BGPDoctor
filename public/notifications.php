<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Controllers\NotificationController;
use App\Core\Auth;
use App\Core\Response;

if (!Auth::check()) {
    Response::redirect('/login.php');
}

$ctrl       = new NotificationController();
$postAction = $_POST['_action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    match ($postAction) {
        'mark_read' => $ctrl->markRead((int) ($_POST['_id'] ?? 0)),
        'clear_all' => $ctrl->clearAll(),
        default     => Response::redirect('/'),
    };
}
Response::redirect('/');