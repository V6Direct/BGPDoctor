<?php

declare(strict_types=1);

namespace App\Models;

final class RouterGroup extends BaseModel
{
    public function all(): array
    {
        return $this->db->query('SELECT * FROM router_groups ORDER BY NAME ASC')->fetchAll();
    }
    public function findById(int $id): ?array
    {
    $stmt = $this->db->prepare('SELECT * FROM router_groups WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    return $stmt->fetch() ?: null;
    }
    public function create(string $name, string $description = ''): int
    {
        $stmt = $this->db->prepare('INSERT INTO router_groups (name, description) VALUES (:name, :description)');
        $stmt->execute(['name' => $name, 'description' => $description]);
        return (int) $this->db->lastInsertId();
    }

    public function delete(int $id): void
    {
        $stmt = $this->db->prepare('DELETE FROM router_groups WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }
}