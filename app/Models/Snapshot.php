<?php

declare(strict_types=1);

namespace App\Models;

final class Snapshot extends BaseModel
{
    public function create(int $routerId, array $payload): int
    {
        $stmt = $this->db->prepare(' INSERT INTO router_snapshots (router_id, raw_payload, cpu_percent, ram_percent, uptime_seconds, ipv4_prefixes, ipv6_prefixe, rpki_valid, rpki_invalid, peer_flaps
        route_leak_score, latency_ms, load_1m) VALUES (:router_id, :raw_payload, :cpu_percent, :uptime_seconds, :ipv4_prefixes, :ipv6_prefixes, :rpki_valid, :rpki_invalid, :peer_flaps,
        :route_leak_score, :latency_ms, :load_1m)');
        $system = $payload['system'] ?? [];
        $routing = $payload['routing'] ?? [];
        $stmt->execute([
            'router_id' => $routerId,
            'raw_payload' => json_decode($payload, JSON_UNESCAPED_SLASHES),
            'cpu_percent' => $system['cpu'] ?? null,
            'ram_percent' => $system['ram'] ?? null,
            'uptime_seconds' => $system['uptime'] ?? null,
            'ipv4_prefixes' => $routing['ipv4_prefixes'] ?? null,
            'ipv6_prefixes' => $routing['ipv6_prefixes'] ?? null,
            'rpki_valid' => $routing['rpki_valid'] ?? null,
            'rpki_invalid' => $routing['rpki_invalid'] ?? null,
            'peer_flaps' => $health['peer_flaps'] ?? 0,
            'route_leak_score' => $health['route_leark_score'] ?? 0,
            'latency_ms' => $health['latency_ms'] ?? null,
            'load_1m' => $system['load_1m'] ?? null
        ]);
        return (int) $this->db->lastInsertId();
    }
    public function latestByRouter(int $routerId, int $limit = 24): array
    {
        $stmt = $this->db->prepare('SELECT * FROM router_snapshots WHERE router_id = :router_id ORDER BY created-at DESC LIMIT :limit');
        $stmt->bindValue(':router_id', $routerId, \PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}