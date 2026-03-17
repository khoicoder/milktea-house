<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$host = "localhost";
$user = "root";
$password = "";
$dbname = "milktea_house";



define("BASE_URL", "/milktea-house/");
$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
// check DB tồn tại chưa
$db_check = mysqli_select_db($conn, $dbname);

if (!$db_check) {
    // nếu chưa có → tạo DB + bảng
    require_once(__DIR__ . "/databases.php");
    new databases();
}

// connect lại với DB
$conn = mysqli_connect($host, $user, $password, $dbname);
