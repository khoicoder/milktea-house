<?php
require_once("../config/config.php");
header('Content-Type: application/json');

// Nhận chuỗi JSON chứa các product_size_id từ Javascript gửi lên
$ids_json = isset($_POST['ids']) ? $_POST['ids'] : '[]';

// Chuyển chuỗi JSON ngược lại thành mảng PHP
$product_size_ids = json_decode($ids_json, true);

// Kiểm tra xem dữ liệu có phải là mảng và không rỗng
if (is_array($product_size_ids) && !empty($product_size_ids)) {
    // Lặp qua tất cả products
    foreach ($_SESSION['cart'] as $prod_id => $sizes) {
        foreach ($product_size_ids as $product_size_id) {
            if (isset($sizes[$product_size_id])) {
                unset($_SESSION['cart'][$prod_id][$product_size_id]);
            }
        }
        // Xóa product_id nếu không còn size nào
        if (empty($_SESSION['cart'][$prod_id])) {
            unset($_SESSION['cart'][$prod_id]);
        }
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

// Trả về kết quả
echo json_encode([
    "status" => "success",
    "message" => "Đã xóa các sản phẩm được chọn",
    "cart_count" => $count
]);