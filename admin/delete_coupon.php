<?php
require_once("../config/config.php");
require_once("auth_admin.php");

if (!isset($_GET['id'])) {
    die("Thiếu ID mã giảm giá");
}

$id = (int) $_GET['id'];

// Thực hiện xóa
mysqli_query($conn, "DELETE FROM coupons WHERE id = $id");

// Quay lại trang quản lý
header("Location: pages/manage_coupons.php");
exit;
