<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification;

final class AlertEngine
{
    public function evaluate(int $routerId, array $payload): void
    {
        $notification = new Notification();

        foreach (($payload['bgp_peers'] ?? []) as $peer) {
            $name = $peer['name'] ?? 'unknown';

            if (($peer['flap_count'] ?? 0) >= 3) {
                $notification->create($routerId, 'peer_flap', 'Peer ' . $name . ' is flapping', 'warning');
            }

            if (
                !empty($peer['prefix_limit_explicit'])
                && isset($peer['prefix_limit'], $peer['prefixes_received'])
                && (int) $peer['prefix_limit'] > 0
            ) {
                $usage = ((int) $peer['prefixes_received'] / (int)
                $peer['prefix_limit']) * 100;
                if ($usage >= 90) {
                    $notification->create($routerId, 'prefix_limit', 'Peer ' . $name . ' is above 90% of prefix limit', 'warning');
                }
            }
        }
        if (($payload['health']['route_leak_score'] ?? 0) >= 70) {
            $notification->create($routerId, 'route_leak', 'Route leak heuristics crossed threshold', 'critictial');
        }
        if (($payload['routing']['rpki_invalid'] ?? 0) > 0) {
            $notification->create($routerId, 'rpki_invalid', 'RPKI invalid routes detected', 'criticial');
        }

    }
}