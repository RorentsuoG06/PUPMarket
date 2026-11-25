<?php
require_once __DIR__ . "/../../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../forgot_password.php");
    exit;
}

$email = strtolower(trim($_POST["email"] ?? ""));
$otp = trim($_POST["otp"] ?? "");
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

$_SESSION["reset_old_email"] = $email;

if ($email === "" || $otp === "" || $newPassword === "" || $confirmPassword === "") {
    header("Location: ../forgot_password.php?status=empty");
    exit;
}

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !str_ends_with($email, "@iskolarngbayan.pup.edu.ph")
) {
    header("Location: ../forgot_password.php?status=email");
    exit;
}

if (!preg_match("/^\d{6}$/", $otp)) {
    header("Location: ../forgot_password.php?status=otp_required");
    exit;
}

$passwordLength = preg_match_all("/./us", $newPassword);
if ($passwordLength === false || $passwordLength < 6) {
    header("Location: ../forgot_password.php?status=password");
    exit;
}

if ($newPassword !== $confirmPassword) {
    header("Location: ../forgot_password.php?status=match");
    exit;
}

$resetStmt = mysqli_prepare(
    $conn,
    "SELECT reset_id, otp_hash, attempts
     FROM password_resets
     WHERE email = ? AND expires_at > NOW()
     ORDER BY reset_id DESC
     LIMIT 1"
);
if (!$resetStmt) {
    mysqli_close($conn);
    header("Location: ../forgot_password.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($resetStmt, "s", $email);
mysqli_stmt_execute($resetStmt);

$resetId = 0;
$otpHash = "";
$attempts = 0;
mysqli_stmt_bind_result($resetStmt, $resetId, $otpHash, $attempts);

if (!mysqli_stmt_fetch($resetStmt)) {
    mysqli_stmt_close($resetStmt);
    mysqli_close($conn);
    header("Location: ../forgot_password.php?status=otp_expired");
    exit;
}
mysqli_stmt_close($resetStmt);

if ((int) $attempts >= 5) {
    mysqli_close($conn);
    header("Location: ../forgot_password.php?status=otp_attempts");
    exit;
}

if (!password_verify($otp, $otpHash)) {
    $attemptStmt = mysqli_prepare(
        $conn,
        "UPDATE password_resets SET attempts = attempts + 1 WHERE reset_id = ?"
    );
    if ($attemptStmt) {
        mysqli_stmt_bind_param($attemptStmt, "i", $resetId);
        mysqli_stmt_execute($attemptStmt);
        mysqli_stmt_close($attemptStmt);
    }
    mysqli_close($conn);
    header("Location: ../forgot_password.php?status=otp_invalid");
    exit;
}

$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
$updateStmt = mysqli_prepare($conn, "UPDATE users SET password = ? WHERE email = ?");
if (!$updateStmt) {
    mysqli_close($conn);
    header("Location: ../forgot_password.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($updateStmt, "ss", $newHash, $email);
if (mysqli_stmt_execute($updateStmt)) {
    mysqli_stmt_close($updateStmt);

    $cleanupStmt = mysqli_prepare($conn, "DELETE FROM password_resets WHERE email = ?");
    if ($cleanupStmt) {
        mysqli_stmt_bind_param($cleanupStmt, "s", $email);
        mysqli_stmt_execute($cleanupStmt);
        mysqli_stmt_close($cleanupStmt);
    }

    unset($_SESSION["reset_old_email"]);
    mysqli_close($conn);
    header("Location: ../login.php?status=password_reset");
    exit;
}

mysqli_stmt_close($updateStmt);
mysqli_close($conn);
header("Location: ../forgot_password.php?status=failed");
exit;
?>
