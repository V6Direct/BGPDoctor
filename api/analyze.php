<?php

declare(strict_types=1);

require __DIR__ . '/../bootstrap/app.php';
require __DIR__ . '/../app/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Response;
use App\Models\Peer;
use App\Models\Report;
use App\Models\Router;
use App\Models\Snapshot;
use App\Services\AIAnalyzer;

if (!Auth::check()) {
    Response::json(['ok' => false, 'message' => 'Unauthorized'], 401);
}

$routerId = (int) ($_GET['router_id'] ?? 0);
$router   = null;
foreach ((new Router())->allWithSummary() as $item) {
    if ((int) $item['id'] === $routerId) {
        $router = $item;
        break;
    }
}
if (!$router) {
    Response::json(['ok' => false, 'message' => 'Router not found'], 404);
}

$snapshot = (new Snapshot())->latestByRouter($routerId, 1)[0] ?? null;
if (!$snapshot) {
    Response::json(['ok' => false, 'message' => 'No snapshot available'], 404);
}

$peers      = (new Peer())->byRouter($routerId);
$markdown   = (new AIAnalyzer())->analyze($router, $snapshot, $peers);
$summary    = 'AI report generated for ' . $router['hostname'];
$riskLevel  = str_contains(strtolower($markdown), 'critical') ? 'critical' : 'medium';
$reportId   = (new Report())->create($routerId, (int)  $snapshot['id'], $markdown, $summary, $riskLevel);
Response::json(['ok' => true, 'report_id' => $reportId, 'summary' => $summary]);