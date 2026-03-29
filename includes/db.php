<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "127.0.0.1";   // IMPORTANT
$user = "vsms_user";
$pass = "vsms_pass_2026";
$db   = "vsms";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("DB ERROR: " . $conn->connect_error);
}
?>

