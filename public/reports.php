<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Controllers\ReportController;
use App\Core\Auth;
use App\Core\Response;

if (!Auth::check()) {
    Response::redirect('/login.php');
}

$ctrl   = new ReportController();
$action = $_GET['action'] ?? 'index';
$id     = isset($_GET['id']) ? (int) $_GET['id'] : null;
$format = $_GET['format'] ?? 'text';

match ($action) {
    'show'  => $ctrl->show((int) ($id ?? 0)),
    'export' => $ctrl->export((int) ($id ?? 0), $format),
    default  => $ctrl->index(),
};