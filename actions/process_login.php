<?php

require "../config/database.php";


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}


$email = strtolower(trim($_POST["email"] ?? ""));
$studentNumber = trim($_POST["student_number"] ?? "");
$password = $_POST["password"] ?? "";


if ($email === "" || $studentNumber === "" || $password === "") {
    header("Location: ../login.php?status=empty");
    exit;
}

if (
    !filter_var($email, FILTER_VALIDATE_EMAIL) ||
    !str_ends_with($email, "@iskolarngbayan.pup.edu.ph")
) {
    header("Location: ../login.php?status=email");
    exit;
}

$query = "SELECT user_id, full_name, username, password
          FROM users
          WHERE email = ? AND student_number = ?
          LIMIT 1";


$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: ../login.php?status=failed");
    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $email,
    $studentNumber
);

mysqli_stmt_execute($stmt);

$userId = 0;
$fullName = "";
$dbUsername = "";
$passwordHash = "";

mysqli_stmt_bind_result(
    $stmt,
    $userId,
    $fullName,
    $dbUsername,
    $passwordHash
);


if (
    mysqli_stmt_fetch($stmt) &&
    password_verify($password, $passwordHash)
) {

    session_regenerate_id(true);

    $_SESSION["user_id"] = $userId;
    $_SESSION["full_name"] = $fullName;
    $_SESSION["username"] = $dbUsername;


    mysqli_stmt_close($stmt);
    mysqli_close($conn);


    header("Location: ../index.php");
    exit;
}


mysqli_stmt_close($stmt);
mysqli_close($conn);

header("Location: ../login.php?status=invalid");
exit;

?>