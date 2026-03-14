<?php
require_once("../config/config.php");
header('Content-Type: application/json');

$id = $_POST['id'] ?? 0;
$qty = $_POST['quantity'] ?? 1;

if (isset($_SESSION['cart'][$id])) {
    if ($qty > 0) {
        $_SESSION['cart'][$id] = $qty; // Cập nhật lại số lượng mới
    } else {
        unset($_SESSION['cart'][$id]);
    }
}

// Đếm lại tổng số lượng trong giỏ để cập nhật Header
$count = empty($_SESSION['cart']) ? 0 : array_sum($_SESSION['cart']);

echo json_encode([
    "status" => "success",
    "cart_count" => $count
]);