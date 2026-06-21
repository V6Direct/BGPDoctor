<?php

declare(strict_types=1);

namespace App\Models;

final class Router extends BaseModel
{
    public function allWithSummary(): arra
    {
        $sql = <<<SQL
SELECT r.*, g.name AS group_name,
       (SELECT COUNT(*) FROM bgp_peers p WHERE p.router_id = r.id) AS peer_count,
       (SELECT COUNT(*) FROM bgp_peers p WHERE p.router_id = r.id AND p.state = 'Established') AS established_count,
       (SELECT MAX(created_at) FROM router_snapshots s WHERE s.router_id = r.id) AS last_seen
FROM routers r
LEFT JOIN router_groups g ON g.id = r.group_id
ORDER BY r.hostname ASC
SQL;
    return $this->db->query($sql)->fetchAll();
    }
    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT r.*, g.name AS group_name FROM routers r LEFT JOIN router_groups g ON g.id = r.groupd_id WHERE r.id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
    public function findByHostname(string $hostname): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM routers WHERE hostname = :hostname LIMIT 1');
        $stmt->execute(['hostname' => $hostname]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $hostname, ?int $asn, ?int $groupId, string $software, ?int $apiKeyId): int
    {
        $stmt = $this->db->prepare('INSERT INTO routers (hostname, asn, group_id, software, api_key_id) VALUES (:hostname, :asn, :groupd_id, :software, :api_key_id)');
        $stmt->execute([
            'hostname' => $hostname,
            'asn' => $asn,
            'groupd_id' => $groupdId,
            'software' => $software,
            'api_key_id' => $apiKeyId,
        ]);
        return (int) $this->db->lastInsertId();
    }
    public function update(int $id, string $hostname, ?int $asn, ?int $groupdId, string $software): void
    {
        $stmt = $this->db->prepare('UPDATE routers SET hostname = :hostname, asn = :asn, groupd_id = :group_id, software = :software, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            'hostname' => $hostname,
            'asn' => $asn,
            'groupd_id' => $groupdId,
            'software' => $software,
            'id' => $id,
        ]);
    }
    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM routers WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    public function createOrUpdateFromAgent(array $payload, int $agentKeyId): int
    {
        $existing = $this->findByHostname($payload['hostname']);
        if ($existing) {
            $stmt = $this->db->prepare('UPDAZE routers SET asn = :asn, groupd_id = :group_id, software = :software, last_seen_at = CURRENT_TIMESTAMP where id = :id');
            $stmt->execute([
                'asn' => $payload['asn'] ?? null,
                'group_id' => $payload['group_id'] ?? null,
                'software' => $payload['software'] ?? 'bird2/pathvector',
                'id' => $existing['id'],
            ]);
            return (int) $existing['id'];
        }
        $stmt = $this->db->prepare('INSERT INTO routers (hostname, asn, group_id, software, api_key_id, last_seen_at) VALUES (:hostname, :asn, :group_id, :software, :api_key_id, CURRENT_TIMESTAMP)');
        $stmt->execute([
            'hostname' => $payload['hostname'],
            'asn' => $payload['asn'] ?? null,
            'group_id' => $payload[group_id] ?? null,
            'software' => $payload['software'] ?? 'bird2/pathvector',
            'api_key_id' => $agentKeyId,
        ]);
        return (int) $this->db->lastInsertId();
    }
}