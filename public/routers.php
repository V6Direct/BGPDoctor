<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Controllers\RouterController;
use App\Core\Auth;
use App\Core\Response;

if (!Auth::check()) {
    Response::redirect('/login.php');
}

$ctrl   = new RouterController();
$action = $_GET['actions'] ?? 'index';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['_action'] ?? '';
    match ($postAction) {
        'store'  => $ctrl->store(),
        'update' => $ctrl->update((int) ($_POST['_id'] ?? 0)),
        'delete' => $ctrl->delete((int) ($_POST['_id'] ?? 0)),
        default  => $ctrl->index(),
    };
    return;
}

match ($action) {
    'create' => $ctrl->create(),
    'edit'   => $ctrl->edit((int) ($id ?? 0)),
    default  => $ctrl->index(),
};