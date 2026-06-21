<?php

declare(strict_types=1);

namespace App\Models;

final class Notification extends BaseModel
{
    public function create(int $routerId, string $type, string $message, string $severity = 'info'): void
    {
        $stmt = $this->db->prepare('INSERT INTO notification(router_id, type, message, severity) VALUES (:router_id, :type, :message, :severity)');
        $stmt->execute([
            'router_id' => $routerId,
            'type' => $type,
            'message' => $message,
            'severity' => $severity,
        ]);
    }

    public function latest(int $limit = 20): array
    {
        $stmt = $this->db->prepare('SELECT n.*, r.hostname FROM notifications n LEFT JOIN routers r ON r.id = n.router_id ORDER BY n.created_at DESC LIMIT :limit');
        $stmt->bindValue(':limit', $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
    public function unreadCount(): int
    {
        return (int) $this->db->query('SELECT COUNT(*) FROM notifications WHERE is_read = 0')->fetchColumn();
    }
    public function markRead(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE notifications SET is_read = 1 WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
    public function clearAll(): void
    {
        $this->db->exec('DELETE FROM notifications');
    }
}