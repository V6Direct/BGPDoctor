<?php

declare(strict_types=1);

namespace App\Models;

final class Peer extends BaseModel
{
    public function replaceForSnapshot(int $routerId, int $snapshotId, array $peers): void
    {
    $delete = $this->db->prepare(
        'DELETE FROM bgp_peers WHERE router_id = :router_id AND snapshot_id = :snapshot_id'
    );

    delete->execute(['router_id' => $routerId, 'snapshot_id' => $snapshotId]);

            $insert = $this->db->prepare(
            'INSERT INTO bgp_peers
             (router_id, snapshot_id, name, state, is_ipv6,
              prefixes_received, prefixes_advertised, rpki_state,
              latency_ms, flap_count, prefix_limit, pathvector_profile)
             VALUES
             (:router_id, :snapshot_id, :name, :state, :is_ipv6,
              :prefixes_received, :prefixes_advertised, :rpki_state,
              :latency_ms, :flap_count, :prefix_limit, :pathvector_profile)'
        );

            foreach ($peers as $peer) {
            $insert->execute([
                'router_id'           => $routerId,
                'snapshot_id'         => $snapshotId,
                'name'                => $peer['name']                ?? 'unknown',
                'state'               => $peer['state']               ?? 'Unknown',
                'is_ipv6'             => !empty($peer['ipv6'])        ? 1 : 0,
                'prefixes_received'   => $peer['prefixes_received']   ?? 0,
                'prefixes_advertised' => $peer['prefixes_advertised'] ?? 0,
                'rpki_state'          => $peer['rpki_state']          ?? 'unknown',
                'latency_ms'          => $peer['latency_ms']          ?? null,
                'flap_count'          => $peer['flap_count']          ?? 0,
                'prefix_limit'        => $peer['prefix_limit']        ?? null,
                'pathvector_profile'  => $peer['pathvector_profile']  ?? null,
            ]);
        }
    }

    public function bySnapshot(int $snapshotId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM bgp_peers
             WHERE snapshot_id = :snapshot_id
             ORDER BY state DESC, name ASC'
        );
        $stmt->execute(['snapshot_id' => $snapshotId]);
        return $stmt->fetchAll();
    }

    public function byRouter(int $routerId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM bgp_peers
             WHERE router_id = :router_id
             ORDER BY state DESC, name ASC'
        );
        $stmt->execute(['router_id' => $routerId]);
        return $stmt->fetchAll();
    }
}