<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Request;
use App\Core\Response;
use App\Models\Peer;
use App\Models\Report;
use App\Models\Router;
use App\Models\Snapshot;
use App\Services\AIAnalyzer;

header('Content-Type: application/json');

if (!Auth::check()) {
    Response::json(['ok' => false, 'message' => 'Unauthorized'], 401);
}

// Accept both POST JSON body and GET query string for flexibility
$isPost   = $_SERVER['REQUEST_METHOD'] === 'POST';
$body     = $isPost ? Request::json() : [];
$routerId = (int) ($body['router_id'] ?? $_GET['router_id'] ?? 0);

// CSRF: required on POST, skip on GET (GET is idempotent read-only trigger from dashboard)
if ($isPost) {
    $token = $body['_csrf'] ?? '';
    if (!Csrf::validate($token)) {
        Response::json(['ok' => false, 'message' => 'CSRF token invalid'], 403);
    }
}

if ($routerId <= 0) {
    Response::json(['ok' => false, 'message' => 'router_id is required'], 422);
}

// Load router
$router = (new Router())->findById($routerId);
if (!$router) {
    Response::json(['ok' => false, 'message' => 'Router not found'], 404);
}

// Must have at least one real snapshot from the agent
$snapshots = (new Snapshot())->latestByRouter($routerId, 1);
$snapshot  = $snapshots[0] ?? null;
if (!$snapshot) {
    Response::json([
        'ok'      => false,
        'message' => 'No snapshot available for this router. '
                   . 'Deploy the BGPDoctor agent and wait for it to post a snapshot first.',
    ], 422);
}

// Peers from this snapshot
$peers = (new Peer())->bySnapshot((int) $snapshot['id']);

try {
    $markdown  = (new AIAnalyzer())->analyze($router, $snapshot, $peers);
} catch (\Throwable $e) {
    Response::json(['ok' => false, 'message' => 'AI analysis failed: ' . $e->getMessage()], 500);
}

$summary   = 'AI report for ' . $router['hostname'] . ' — ' . date('Y-m-d H:i');
$riskLevel = match (true) {
    str_contains(strtolower($markdown), 'critical') => 'critical',
    str_contains(strtolower($markdown), 'warning')  => 'warning',
    default                                          => 'medium',
};

$reportId = (new Report())->create(
    $routerId,
    (int) $snapshot['id'],
    $markdown,
    $summary,
    $riskLevel
);

Response::json([
    'ok'           => true,
    'report_id'    => $reportId,
    'summary'      => $summary,
    'risk_level'   => $riskLevel,
    'redirect_url' => '/reports.php?action=show&id=' . $reportId,
]);
