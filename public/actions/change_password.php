<?php
require_once __DIR__ . "/../../src/Middleware/auth.php";
require_once __DIR__ . "/../../config/database.php";

requireAuth("../login.php");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../profile.php");
    exit;
}

$userId = (int) $_SESSION["user_id"];
$currentPassword = $_POST["current_password"] ?? "";
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if ($currentPassword === "" || $newPassword === "" || $confirmPassword === "") {
    header("Location: ../profile.php?status=pw_empty");
    exit;
}

$passwordLength = preg_match_all("/./us", $newPassword);
if ($passwordLength === false || $passwordLength < 6) {
    header("Location: ../profile.php?status=pw_length");
    exit;
}

if ($newPassword !== $confirmPassword) {
    header("Location: ../profile.php?status=pw_match");
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE user_id = ? LIMIT 1");
if (!$stmt) {
    mysqli_close($conn);
    header("Location: ../profile.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$passwordHash = "";
mysqli_stmt_bind_result($stmt, $passwordHash);

if (!mysqli_stmt_fetch($stmt) || !password_verify($currentPassword, $passwordHash)) {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: ../profile.php?status=pw_current");
    exit;
}
mysqli_stmt_close($stmt);

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE user_id = ?");
if (!$updateStmt) {
    mysqli_close($conn);
    header("Location: ../profile.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($updateStmt, "si", $newHash, $userId);
if (mysqli_stmt_execute($updateStmt)) {
    mysqli_stmt_close($updateStmt);
    mysqli_close($conn);
    header("Location: ../profile.php?status=password_updated");
    exit;
}

mysqli_stmt_close($updateStmt);
mysqli_close($conn);
header("Location: ../profile.php?status=failed");
exit;
?>
