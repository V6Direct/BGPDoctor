<?php 

declare(strict_types=1);

namespace App\Services;

use App\Config\Database;

final class RateLimiter
{
    public function allow(string $key, int $limit, int $windowSeconds): bool
    {
        $db = Database::connection();
        $db->exec('CREATE TABLE IF NOT EXISTS rate_limits (rate_key TEXT PRIMARY KEY, count INTEGER NOT NULL, reset_at INTEGER NOT NULL)');

        $now = time();
        $stmt = $db->prepare('SELECT * FROM rate_limits WHERE rate_key = :rate_key LIMIT 1');
        $stmt->execute(['rate_key' => $key]);
        $row = $stmt->fetch();

        if (!$row || (int) $row['reset_at'] <= $now) {
            $write = $db->prepare('REPLACE INTO rate_limits (rate_key, count, reset_at) VALUES (:rate_key, 1, :reset_at)');
            $write->execute(['rate_key' => $key, 'reset_at' => $now +
            $windowSeconds]);
            return true;
        }

        if ((int) $row['count'] >= $limit) {
            return false;
        }

        $write = $db->prepare('UPDATE rate_limits SET count = count +1 WHERE rate_key = :rate_ley');
        $write->execute(['rate_key' => $key]);
        return true;
    }
}