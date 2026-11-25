<?php
require_once __DIR__ . "/../src/Middleware/auth.php";
ensureSessionStarted();

if (isset($_COOKIE["pupmarket_remember"])) {
    $connection = rememberDatabaseConnection();
    if (!forgetRememberedLogin($connection)) {
        error_log("Sign-out completed with a remember token that could not be revoked.");
    }
}

$_SESSION = [];
session_destroy();

header("Location: login.php?logout=1");
exit;
?>
