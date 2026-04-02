<?php
require_once("../config/config.php");
header('Content-Type: application/json');

$product_size_id = intval($_POST['id'] ?? 0);
$qty = intval($_POST['quantity'] ?? 1);

// Kiểm tra product_size_id có tồn tại không
if ($product_size_id <= 0) {
    echo json_encode([
        "status" => "error",
        "message" => "Size không hợp lệ"
    ]);
    exit;
}

// Lấy thông tin stock từ product_sizes
$size_result = mysqli_query($conn, "SELECT stock FROM product_sizes WHERE id = $product_size_id");
$size_info = mysqli_fetch_assoc($size_result);

if (!$size_info) {
    echo json_encode([
        "status" => "error",
        "message" => "Size không tồn tại"
    ]);
    exit;
}

if ($qty > $size_info['stock']) {
    echo json_encode([
        "status" => "error",
        "message" => "Sản phẩm này chỉ còn " . $size_info['stock'] . " sản phẩm.",
        "max_stock" => $size_info['stock']
    ]);
    exit;
}

// Tìm product_id từ product_size_id
foreach ($_SESSION['cart'] as $prod_id => $sizes) {
    if (isset($sizes[$product_size_id])) {
        if ($qty > 0) {
            $_SESSION['cart'][$prod_id][$product_size_id] = $qty;
        } else {
            unset($_SESSION['cart'][$prod_id][$product_size_id]);
            // Xóa product_id nếu không còn size nào
            if (empty($_SESSION['cart'][$prod_id])) {
                unset($_SESSION['cart'][$prod_id]);
            }
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