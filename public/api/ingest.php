<?php

declare(strict_types=1);

require __DIR__ . '/../../bootstrap/app.php';
require BASE_PATH . '/app/Helpers/functions.php';

use App\Config\Env;
use App\Core\Request;
use App\Core\Response;
use App\Services\IngestService;
use App\Services\RateLimiter;

header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Headers: Content-Type, X-API-Key, X-Agent-Hostname');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$apiKey   = $_SERVER['HTTP_X_API_KEY']        ?? '';
$hostname = $_SERVER['HTTP_X_AGENT_HOSTNAME'] ?? 'unknown';
$limiter  = new RateLimiter();
$limit    = (int) Env::get('AGENT_DEFAULT_RATE_LIMIT', '60');
$window   = (int) Env::get('API_RATE_LIMIT_WINDOW',    '60');
if (!$limiter->allow('agent:' . sha1($hostname . $apiKey), $limit, $window)) {
    Response::json(['ok' => false, 'message' => 'Rate limit exceeded'], 429);
}

$payload = Request::json();
if (empty($payload['hostname']) || !isset($payload['bgp_peers']) || !isset($payload['system'])) {
    Response::json(['ok' => false, 'message' => 'Invalid payload'], 422);
}

$result = (new IngestService())->ingest($payload, $apiKey);
Response::json($result, $result['status']);
