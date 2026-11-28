<?php
declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use RuntimeException;

class Database
{
    private static ?self $instance = null;
    private PDO $pdo;
    private string $driver;

    private function __construct()
    {
        $driver = strtolower((string) env("DB_CONNECTION", "mysql"));
        $this->driver = $driver === "pgsql" || $driver === "postgres" ? "pgsql" : "mysql";

        if ($this->driver === "pgsql") {
            $host = (string) env("SUPABASE_DB_HOST", env("DB_HOST", "localhost"));
            $port = (string) env("SUPABASE_DB_PORT", env("DB_PORT", "5432"));
            $dbname = (string) env("SUPABASE_DB_NAME", env("DB_NAME", "postgres"));
            $user = (string) env("SUPABASE_DB_USER", env("DB_USER", "postgres"));
            $pass = (string) env("SUPABASE_DB_PASS", env("DB_PASS", ""));
            $sslmode = (string) env("SUPABASE_DB_SSLMODE", "require");
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode={$sslmode}";
        } else {
            $host = (string) env("DB_HOST", "localhost");
            $port = (string) env("DB_PORT", "3306");
            $dbname = (string) env("DB_NAME", "db_pupmarket");
            $user = (string) env("DB_USER", "root");
            $pass = (string) env("DB_PASS", "");
            $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
        }

        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            throw new RuntimeException("Unable to connect to the database.", 0, $e);
        }
    }

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection(): PDO
    {
        return $this->pdo;
    }

    public function getDriver(): string
    {
        return $this->driver;
    }
}
