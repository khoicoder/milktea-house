<?php
session_start();
require_once(__DIR__ . "/databases.php");
define("BASE_URL", "http://localhost/milktea-house/");
// tạo biến $conn global
$db = new Database();
$conn = $db->conn; 


// http://localhost/milktea-house/config/migrate.php