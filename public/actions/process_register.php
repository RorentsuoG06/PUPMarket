<?php
require_once __DIR__ . "/../../config/database.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}

$fullName = trim($_POST["full_name"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";
$otp = trim($_POST["otp"] ?? "");

$_SESSION["register_old"] = [
    "full_name" => $fullName,
    "email" => $email
];

if (
    $fullName === "" ||
    $email === "" ||
    $password === "" ||
    $confirmPassword === "" ||
    $otp === ""
) {
    header("Location: ../register.php?status=empty");
    exit;
}

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !str_ends_with($email, "@iskolarngbayan.pup.edu.ph")
) {
    header("Location: ../register.php?status=email");
    exit;
}

$passwordLength = preg_match_all("/./us", $password);

if ($passwordLength === false || $passwordLength < 6) {
    header("Location: ../register.php?status=password");
    exit;
}

if ($password !== $confirmPassword) {
    header("Location: ../register.php?status=match");
    exit;
}

if (!preg_match("/^\d{6}$/", $otp)) {
    header("Location: ../register.php?status=otp_required");
    exit;
}

/* Check whether the institutional email is already registered. */
$checkQuery = "SELECT user_id
               FROM users
               WHERE email = ?
               LIMIT 1";

$checkStmt = mysqli_prepare($conn, $checkQuery);

if (!$checkStmt) {
    mysqli_close($conn);
    header("Location: ../register.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($checkStmt, "s", $email);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);
    header("Location: ../register.php?status=exists");
    exit;
}

mysqli_stmt_close($checkStmt);

/* Verify the latest unexpired OTP for this email. */
$otpQuery = "SELECT otp_id, otp_hash, attempts
             FROM email_otps
             WHERE email = ? AND expires_at > NOW()
             ORDER BY otp_id DESC
             LIMIT 1";

$otpStmt = mysqli_prepare($conn, $otpQuery);

if (!$otpStmt) {
    mysqli_close($conn);
    header("Location: ../register.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($otpStmt, "s", $email);
mysqli_stmt_execute($otpStmt);

$otpId = 0;
$otpHash = "";
$attempts = 0;

mysqli_stmt_bind_result($otpStmt, $otpId, $otpHash, $attempts);

if (!mysqli_stmt_fetch($otpStmt)) {
    mysqli_stmt_close($otpStmt);
    mysqli_close($conn);
    header("Location: ../register.php?status=otp_expired");
    exit;
}

mysqli_stmt_close($otpStmt);

if ((int) $attempts >= 5) {
    mysqli_close($conn);
    header("Location: ../register.php?status=otp_attempts");
    exit;
}

if (!password_verify($otp, $otpHash)) {
    $attemptStmt = mysqli_prepare(
        $conn,
        "UPDATE email_otps SET attempts = attempts + 1 WHERE otp_id = ?"
    );
    if ($attemptStmt) {
        mysqli_stmt_bind_param($attemptStmt, "i", $otpId);
        mysqli_stmt_execute($attemptStmt);
        mysqli_stmt_close($attemptStmt);
    }
    mysqli_close($conn);
    header("Location: ../register.php?status=otp_invalid");
    exit;
}

/* Keep the existing unique username column without requiring a separate username. */
$username = "pup_" . bin2hex(random_bytes(12));

/* Password is hashed before it is stored in the database. */
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO users
          (full_name, email, username, password)
          VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    mysqli_close($conn);
    header("Location: ../register.php?status=failed");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $fullName,
    $email,
    $username,
    $passwordHash
);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);

    $cleanupStmt = mysqli_prepare($conn, "DELETE FROM email_otps WHERE email = ?");
    if ($cleanupStmt) {
        mysqli_stmt_bind_param($cleanupStmt, "s", $email);
        mysqli_stmt_execute($cleanupStmt);
        mysqli_stmt_close($cleanupStmt);
    }

    unset($_SESSION["register_old"]);
    mysqli_close($conn);
    header("Location: ../login.php?registered=1");
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
header("Location: ../register.php?status=failed");
exit;
?>
