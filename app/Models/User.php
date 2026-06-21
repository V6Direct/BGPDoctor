<?php

declare(strict_types=1);

namespace App\Models;

final class User extends BaseModel
{

    public function findById(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE ID = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => strtolower($email)]);
        return $stmt->fetch() ?: null;
    }

    public function create(string $name, string $email, string $password, string $role = 'viewer'): void
    {
        $stmt = $this->db->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, :role)');
        $stmt->execute([
            'name' => $name,
            'email' => strtolower($email),
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => $role,
        ]);
    }

    public function updatePassword(int $id, string $newPassword): void
    {
        $stmt = $this->db->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => password_hash($newPassword,
        PASSWORD_DEFAULT), 'id' => $id]);
    }

    public function updateProfile(int $id, string $name, string $email): void
    {
        $stmt = $this->db->prepare('UPDATE users SET name = :name, email = :email WHERE id = :id');
        $stmt->execute(['name' => $name, 'email' => strtolower($email), 'id' => $id]);
    }
}