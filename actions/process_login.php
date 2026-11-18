<?php

require "../config/database.php";


if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../login.php");
    exit;
}


$username = trim($_POST["username"] ?? "");
$password = $_POST["password"] ?? "";


if ($username === "" || $password === "") {
    header("Location: ../login.php?status=empty");
    exit;
}

$query = "SELECT user_id, full_name, username, password
          FROM users
          WHERE username = ? OR email = ?
          LIMIT 1";


$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: ../login.php?status=failed");
    exit;
}


/* Use the entered username/email for both parameters */

mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $username,
    $username
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