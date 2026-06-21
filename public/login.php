<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Controllers\AuthController;

$controller = new AuthController();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->login();
    return;
}
$controller->showLogin();