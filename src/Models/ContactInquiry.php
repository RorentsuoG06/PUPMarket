<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;

class ContactInquiry extends Model
{
    public function create(string $name, string $email, string $subject, string $message): bool
    {
        $stmt = $this->db()->prepare(
            "INSERT INTO inquiries (name, email, subject, message)
             VALUES (:name, :email, :subject, :message)"
        );

        return $stmt->execute([
            "name" => $name,
            "email" => $email,
            "subject" => $subject,
            "message" => $message,
        ]);
    }
}
