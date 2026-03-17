<?php
require_once("../config/config.php");
require_once("auth_admin.php");

// check tồn tại
if (!isset($_GET['id'])) {
    die("Thiếu ID");
}

$id = (int) $_GET['id']; // ép kiểu chống SQL injection

mysqli_query($conn, "DELETE FROM users WHERE id = $id");

// quay lại trang users
header("Location: pages/users.php");
exit;