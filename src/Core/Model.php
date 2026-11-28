<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

abstract class Model
{
    private ?PDO $connection = null;

    protected function db(): PDO
    {
        if ($this->connection === null) {
            $this->connection = Database::getInstance()->getConnection();
        }
        return $this->connection;
    }
}
