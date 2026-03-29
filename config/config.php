<?php
session_start();
require_once(__DIR__ . "/databases.php");
define("BASE_URL", "http://localhost/milktea-house/");
define("UPLOAD_PATH", "http://localhost/milktea-house/uploads/");
define("BANK_CODE", "970415"); // VietinBank
define("BANK_ACCOUNT", "100882563121");
define("BANK_NAME", "LE MINH KHOI");
// tạo biến $conn global
$db = new Database();
$conn = $db->conn; 


// http://localhost/milktea-house/config/migrate.php