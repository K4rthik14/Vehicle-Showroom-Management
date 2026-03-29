<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "root123";  // <-- change if different
$db   = "vsms";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB ERROR: " . $conn->connect_error);
}
?>