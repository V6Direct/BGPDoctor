<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Controllers\SettingsController;
use App\Core\Auth;
use App\Core\Response;

if (!Auth::check()) {
    Response::redirect('/login.php');
}

$ctrl   = new SettingsController();
$action = $_GET['action'] ?? 'index';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['_action'] ?? '';
    match ($postAction) {
        'update_password' => $ctrl->updatePassword(),
        'create_api_key'  => $ctrl->createApiKey(),
        'revoke_api_key'  => $ctrl->revokeApiKey((int) ($_POST['_id'] ?? 0)),
        default           => $ctrl->index(),
    };
    return;
}

$ctrl->index();