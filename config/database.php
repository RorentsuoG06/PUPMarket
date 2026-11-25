<?php
require_once __DIR__ . "/env.php";

/* IskoFinds Database Connection */
$host = env("DB_HOST", "localhost");
$username = env("DB_USER", "root");
$password = env("DB_PASS", "");
$dbname = env("DB_NAME", "db_pupmarket");

$conn = mysqli_connect($host, $username, $password, $dbname);

if (!$conn) {
    die("Unable to connect to the database.");
}

mysqli_set_charset($conn, "utf8mb4");
?>
