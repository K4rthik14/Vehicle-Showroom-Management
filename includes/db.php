<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'vsms');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("<div class='alert alert-danger m-4'>Database Connection Failed: " . $conn->connect_error . "</div>");
}

$conn->set_charset("utf8mb4");
?>
