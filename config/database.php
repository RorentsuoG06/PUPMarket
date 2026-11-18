<?php
/* PUPMarket Database Connection */

$host = "localhost";
$username = "root";
$password = "";
$dbname = "db_pupmarket";

$conn = mysqli_connect($host,$username,$password,$dbname);

if (!$conn) {
    die("Unable to connect to the database.");
}

mysqli_set_charset($conn, "utf8mb4");

?>
