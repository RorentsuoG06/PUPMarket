<?php
require_once "../config/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../register.php");
    exit;
}

$fullName = trim($_POST["full_name"] ?? "");
$studentNumber = trim($_POST["student_number"] ?? "");
$email = trim($_POST["email"] ?? "");
$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

if (
    $fullName === "" ||
    $studentNumber === "" ||
    $email === "" ||
    $username === "" ||
    $password === "" ||
    $confirmPassword === ""
) {
    header("Location: ../register.php?status=empty");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../register.php?status=email");
    exit;
}

if (strlen($password) < 6) {
    header("Location: ../register.php?status=password");
    exit;
}

if ($password !== $confirmPassword) {
    header("Location: ../register.php?status=match");
    exit;
}

/* Check if email, username, or student number already exists. */
$checkQuery = "SELECT user_id
               FROM users
               WHERE email = ? OR username = ? OR student_number = ?
               LIMIT 1";

$checkStmt = mysqli_prepare($conn, $checkQuery);

if (!$checkStmt) {
    header("Location: ../register.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($checkStmt, "sss", $email, $username, $studentNumber);
mysqli_stmt_execute($checkStmt);
mysqli_stmt_store_result($checkStmt);

if (mysqli_stmt_num_rows($checkStmt) > 0) {
    mysqli_stmt_close($checkStmt);
    mysqli_close($conn);
    header("Location: ../register.php?status=exists");
    exit;
}

mysqli_stmt_close($checkStmt);

/* Password is hashed before it is stored in the database. */
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO users
          (full_name, student_number, email, username, password)
          VALUES (?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    mysqli_close($conn);
    header("Location: ../register.php?status=failed");
    exit;
}

mysqli_stmt_bind_param(
    $stmt,
    "sssss",
    $fullName,
    $studentNumber,
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
