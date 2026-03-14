<?php
session_start();
define('BASE_URL', '/vsms/');
session_destroy();
header("Location: " . BASE_URL . "index.php");
exit();