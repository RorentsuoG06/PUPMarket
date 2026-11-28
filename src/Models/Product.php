<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Model;
use Throwable;

class Product extends Model
{
    public function getLatest(int $limit = 8): array
    {
        try {
            $stmt = $this->db()->prepare(
                "SELECT l.listing_id, l.name, l.price, l.category, l.item_condition, l.description, l.image_path, u.full_name AS seller_name
                 FROM marketplace_listings l
                 INNER JOIN users u ON u.user_id = l.seller_user_id
                 ORDER BY l.created_at DESC, l.listing_id DESC
                 LIMIT :limit"
            );
            $stmt->bindValue(":limit", max(1, $limit), \PDO::PARAM_INT);
            $stmt->execute();
            $rows = $stmt->fetchAll();
            if ($rows !== []) {
                return $rows;
            }
        } catch (Throwable $e) {
            error_log("Product catalog fallback active: " . $e->getMessage());
        }

        return $this->defaultFallback();
    }

    private function defaultFallback(): array
    {
        return [
            ["name" => "Data Structures in Java Book", "price" => 550, "category" => "Books", "item_condition" => "Good", "image_path" => "images/datastructuresinjava.jpg"],
            ["name" => "Modern Database Management Book", "price" => 780, "category" => "Books", "item_condition" => "Good", "image_path" => "images/mdm.jpg"],
            ["name" => "OOP Reviewer", "price" => 50, "category" => "Books", "item_condition" => "Like New", "image_path" => "images/oopreviewer.jpg"],
            ["name" => "PUPBC Uniform Set - Women", "price" => 800, "category" => "Uniforms", "item_condition" => "Like New", "image_path" => "images/unifset.jpg"],
        ];
    }
}
