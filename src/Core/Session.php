<?php
declare(strict_types=1);

namespace App\Core;

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function setFlash(string $key, string $message, string $type = "info"): void
    {
        self::start();
        $_SESSION["_flash"][$key] = [
            "message" => $message,
            "type" => $type,
        ];
    }

    public static function getFlash(string $key): ?array
    {
        self::start();
        if (!isset($_SESSION["_flash"][$key])) {
            return null;
        }

        $flash = $_SESSION["_flash"][$key];
        unset($_SESSION["_flash"][$key]);
        return $flash;
    }

    public static function csrfToken(): string
    {
        self::start();
        if (empty($_SESSION["csrf_token"])) {
            $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
        }
        return (string) $_SESSION["csrf_token"];
    }

    public static function isValidCsrf(?string $candidate): bool
    {
        self::start();
        return isset($_SESSION["csrf_token"]) &&
            is_string($candidate) &&
            hash_equals($_SESSION["csrf_token"], $candidate);
    }

    public static function isAuthenticated(): bool
    {
        if (function_exists("restoreRememberedSession")) {
            restoreRememberedSession();
        }
        return isset($_SESSION["user_id"]);
    }

    public static function userId(): ?int
    {
        return self::isAuthenticated() ? (int) $_SESSION["user_id"] : null;
    }

    public static function isAdmin(): bool
    {
        if (function_exists("isAdmin")) {
            return isAdmin();
        }
        return false;
    }

    public static function requireAuth(string $redirectTo = "login.php"): void
    {
        if (!self::isAuthenticated()) {
            header("Location: " . $redirectTo);
            exit;
        }
    }

    public static function requireAdmin(): void
    {
        if (!self::isAdmin()) {
            http_response_code(403);
            exit("Administrator access required.");
        }
    }

    public static function guestOnly(string $redirectTo = "index.php"): void
    {
        if (self::isAuthenticated()) {
            header("Location: " . $redirectTo);
            exit;
        }
    }
}
