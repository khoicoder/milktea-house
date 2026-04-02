<?php
require_once("../config/config.php");
header('Content-Type: application/json');

$product_size_id = intval($_POST['id'] ?? 0);

if ($product_size_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Size không hợp lệ"
    ]);
    exit;
}

// Tìm và xóa product_size_id
foreach ($_SESSION['cart'] as $prod_id => $sizes) {
    if (isset($sizes[$product_size_id])) {
        unset($_SESSION['cart'][$prod_id][$product_size_id]);
        // Xóa product_id nếu không còn size nào
        if (empty($_SESSION['cart'][$prod_id])) {
            unset($_SESSION['cart'][$prod_id]);
        }
        break;
    }
}

// Đếm lại tổng số lượng từ tất cả sizes
$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $product_id => $product_sizes) {
        if (is_array($product_sizes)) {
            foreach ($product_sizes as $qty) {
                $count += (int)$qty;
            }
        }
    }
}

echo json_encode([
    "status" => "success",
    "cart_count" => $count
]);