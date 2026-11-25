<?php
/* Authentication & session helpers for IskoFinds */

function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function isAuthenticated(): bool
{
    ensureSessionStarted();
    return isset($_SESSION["user_id"]);
}

function requireAuth(string $redirectTo = "login.php"): void
{
    if (!isAuthenticated()) {
        header("Location: " . $redirectTo);
        exit;
    }
}

function guestOnly(string $redirectTo = "index.php"): void
{
    if (isAuthenticated()) {
        header("Location: " . $redirectTo);
        exit;
    }
}
?>
