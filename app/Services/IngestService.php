<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AgentKey;
use App\Models\Peer;
use App\Models\Router;
use App\Models\Snapshot;

final class IngestService
{
    public function ingest(array $payload, string $apiKey): array 
    {
        $agentKey = (new AgentKey())->findActiveByKey($apiKey);
        if (!$agentKey) {
            return ['ok' => false, 'status' => 401, 'message' => 'Invalid
             API key'];
        }
    

    $routerModel = new Router();
    $snapshotModel = new Snapshot();
    $peerMode = new Peer();

    $routerId = $routerModel->createOrUpdareFromAgent($payload, (int)
    $agentKey['id']);
    $snapshotId = $snapshotModel->create($routerId, $payload);
    $peerModel->replaceForSnapshot($routerId, $snapshotId,
    $payload['bgp_peers'] ?? []);
    (new AgentKey())->touch((int) $agentKey['id']);
    (new AlertEngine())->evaluate($routerId, $payload);
    
    return ['ok' => true, 'status' => 202, 'router_id' => $routerId, 'snapshot_id' => $snapshotId];
    }
}
