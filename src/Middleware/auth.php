<?php
/* Authentication & session helpers for IskoFinds */

function ensureSessionStarted(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function rememberDatabaseConnection(): mysqli
{
    global $conn;

    if (!isset($conn) || !($conn instanceof mysqli)) {
        require_once __DIR__ . "/../../config/database.php";
    }

    return $conn;
}

function rememberCookieOptions(int $expires): array
{
    return [
        "expires" => $expires,
        "path" => "/",
        "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
        "httponly" => true,
        "samesite" => "Lax"
    ];
}

function forgetRememberedLogin(mysqli $connection): bool
{
    if (!isset($_COOKIE["pupmarket_remember"])) {
        return true;
    }

    $cookieValue = (string) $_COOKIE["pupmarket_remember"];
    $parts = explode(":", $cookieValue, 2);
    $success = true;

    if (isset($parts[0]) && preg_match('/^[a-f0-9]{32}$/', $parts[0])) {
        $stmt = mysqli_prepare($connection, "DELETE FROM remember_tokens WHERE selector = ?");
        if (!$stmt) {
            error_log("Unable to prepare remember-token revocation.");
            $success = false;
        } else {
            mysqli_stmt_bind_param($stmt, "s", $parts[0]);
            if (!mysqli_stmt_execute($stmt)) {
                error_log("Unable to revoke remember token: " . mysqli_stmt_error($stmt));
                $success = false;
            }
            mysqli_stmt_close($stmt);
        }
    }

    if (!setcookie("pupmarket_remember", "", rememberCookieOptions(time() - 3600))) {
        error_log("Unable to clear the remember-me cookie.");
        $success = false;
    }
    unset($_COOKIE["pupmarket_remember"]);

    return $success;
}

function issueRememberedLogin(mysqli $connection, int $userId): bool
{
    if (!forgetRememberedLogin($connection)) {
        return false;
    }

    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash("sha256", $validator);
    $expiresAt = gmdate("Y-m-d H:i:s", time() + (30 * 24 * 60 * 60));

    $stmt = mysqli_prepare(
        $connection,
        "INSERT INTO remember_tokens (selector, token_hash, user_id, expires_at)
         VALUES (?, ?, ?, ?)"
    );
    if (!$stmt) {
        error_log("Unable to prepare remember-token creation.");
        return false;
    }

    mysqli_stmt_bind_param($stmt, "ssis", $selector, $tokenHash, $userId, $expiresAt);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Unable to create remember token: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return false;
    }
    mysqli_stmt_close($stmt);

    $cookieValue = $selector . ":" . $validator;
    if (!setcookie("pupmarket_remember", $cookieValue, rememberCookieOptions(time() + (30 * 24 * 60 * 60)))) {
        error_log("Unable to set the remember-me cookie.");
        $deleteStmt = mysqli_prepare($connection, "DELETE FROM remember_tokens WHERE selector = ?");
        if ($deleteStmt) {
            mysqli_stmt_bind_param($deleteStmt, "s", $selector);
            mysqli_stmt_execute($deleteStmt);
            mysqli_stmt_close($deleteStmt);
        } else {
            error_log("Unable to remove a remember token after cookie creation failed.");
        }
        return false;
    }

    $_COOKIE["pupmarket_remember"] = $cookieValue;
    return true;
}

function restoreRememberedSession(): void
{
    ensureSessionStarted();

    if (isset($_SESSION["user_id"]) || !isset($_COOKIE["pupmarket_remember"])) {
        return;
    }

    $parts = explode(":", (string) $_COOKIE["pupmarket_remember"], 2);
    if (
        count($parts) !== 2 ||
        !preg_match('/^[a-f0-9]{32}$/', $parts[0]) ||
        !preg_match('/^[a-f0-9]{64}$/', $parts[1])
    ) {
        if (!setcookie("pupmarket_remember", "", rememberCookieOptions(time() - 3600))) {
            error_log("Unable to clear a malformed remember-me cookie.");
        }
        unset($_COOKIE["pupmarket_remember"]);
        return;
    }

    $connection = rememberDatabaseConnection();
    $stmt = mysqli_prepare(
        $connection,
        "SELECT rt.user_id, rt.token_hash, u.full_name, u.username
         FROM remember_tokens rt
         INNER JOIN users u ON u.user_id = rt.user_id
         WHERE rt.selector = ? AND rt.expires_at > UTC_TIMESTAMP()
         LIMIT 1"
    );
    if (!$stmt) {
        error_log("Unable to prepare remember-token lookup.");
        return;
    }

    mysqli_stmt_bind_param($stmt, "s", $parts[0]);
    if (!mysqli_stmt_execute($stmt)) {
        error_log("Unable to look up remember token: " . mysqli_stmt_error($stmt));
        mysqli_stmt_close($stmt);
        return;
    }

    $userId = 0;
    $tokenHash = "";
    $fullName = "";
    $username = "";
    mysqli_stmt_bind_result($stmt, $userId, $tokenHash, $fullName, $username);
    $found = mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if (!$found || !hash_equals($tokenHash, hash("sha256", $parts[1]))) {
        if (!forgetRememberedLogin($connection)) {
            error_log("Unable to clear an invalid remember token.");
        }
        return;
    }

    session_regenerate_id(true);
    $_SESSION["user_id"] = $userId;
    $_SESSION["full_name"] = $fullName;
    $_SESSION["username"] = $username;

    if (!issueRememberedLogin($connection, (int) $userId)) {
        error_log("Remember token rotation failed; the current session remains authenticated.");
    }
}

function isAuthenticated(): bool
{
    restoreRememberedSession();
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
