<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}

$fullName = trim($_POST["full_name"] ?? "");
$email = strtolower(trim($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if (
    $fullName === "" ||
    $email === "" ||
    $password === "" ||
    $confirmPassword === ""
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

/* Check whether the institutional email is already registered. */
$checkQuery = "SELECT user_id
               FROM users
               WHERE email = ?
               LIMIT 1";

$checkStmt = mysqli_prepare($conn, $checkQuery);

if (!$checkStmt) {
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
    mysqli_close($conn);
    header("Location: ../login.php?registered=1");
    exit;
}

mysqli_stmt_close($stmt);
mysqli_close($conn);
header("Location: ../register.php?status=failed");
exit;
?>
