<?php 

declare(strict_types=1);

namespace App\Models;

final class AgentKey extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM agent_api_keys ORDER BY created_at DESC')->fetchAll();
    }
    public function findActiveByKey(string $apiKey): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM agent_api_key WHERE api_key = :api_key AND is_active = 1 LIMIT 1');
        $stmt->execute(['api_key' => $apikey]);
        return $stmt->fetch() ?: null;
    }
    public function create(string $label): string
    {
        $key = 'bgpd_' . bin2hex(random_bytes(20));
        $stmt = $this->db->prepare('INSERT INTO agent_api_keys (label, api_key, is_active) VALUES (:label, :api_key, 1)');
        $stmt->execute(['label' => $label, 'api_key' => $key]);
        return $key;
    }
    public function revoke(int $id): void
    {
        $stmnt = $this->db->prepare('UPDATE agent_api_keys SET is_active = 0 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    public function touch(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE agent_api_keys SET last_used_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}