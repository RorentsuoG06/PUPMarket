<?php
require "../actions/database.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../contact.php");
    exit;
}

$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$subject = trim($_POST["subject"] ?? "");
$message = trim($_POST["message"] ?? "");

if ($name === "" || $email === "" || $subject === "" || $message === "") {
    header("Location: ../contact.php?status=empty");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: ../contact.php?status=email");
    exit;
}

$query = "INSERT INTO inquiries (name, email, subject, message)
          VALUES (?, ?, ?, ?)";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    header("Location: ../contact.php?status=failed");
    exit;
}

mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $subject, $message);

if (mysqli_stmt_execute($stmt)) {
    mysqli_stmt_close($stmt);
    if (isset($conn) && $conn instanceof mysqli) {
        mysqli_close($conn);
    }
    header("Location: ../contact.php?status=success");
    exit;
}

mysqli_stmt_close($stmt);
if (isset($conn) && $conn instanceof mysqli) {
    mysqli_close($conn);
}
header("Location: ../contact.php?status=failed");
exit;
?>
