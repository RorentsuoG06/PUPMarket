<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    public function findById(int $userId): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT user_id, full_name, email, username, course_year, contact_info, bio, avatar_path, is_admin, created_at
             FROM users
             WHERE user_id = :id
             LIMIT 1"
        );
        $stmt->execute(["id" => $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->db()->prepare(
            "SELECT user_id, full_name, email, username, password, is_admin
             FROM users
             WHERE email = :email
             LIMIT 1"
        );
        $stmt->execute(["email" => strtolower(trim($email))]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function isAdmin(int $userId): bool
    {
        $stmt = $this->db()->prepare("SELECT is_admin FROM users WHERE user_id = :id LIMIT 1");
        $stmt->execute(["id" => $userId]);
        $value = $stmt->fetchColumn();
        return (int) $value === 1;
    }
}
