<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$product_size_ids = $data['product_size_ids'] ?? [];
$coupon_id = $data['coupon_id'] ?? null;
$discount_amount = $data['discount_amount'] ?? 0;

if (empty($product_size_ids)) {
    echo json_encode(["success" => false, "message" => "Vui lòng chọn sản phẩm"]);
    exit;
}

// Convert product_size_ids to [$product_id, $product_size_id, $qty] tuples
$checkout_items = [];
foreach ($product_size_ids as $product_size_id) {
    $product_size_id = (int)$product_size_id;
    // Lấy product_id từ product_size_id
    $result = mysqli_query($conn, "SELECT product_id FROM product_sizes WHERE id = $product_size_id");
    $row = mysqli_fetch_assoc($result);
    if ($row) {
        $product_id = (int)$row['product_id'];
        // Lấy quantity từ cart
        $qty = $_SESSION['cart'][$product_id][$product_size_id] ?? 0;
        $checkout_items[] = [$product_id, $product_size_id, $qty];
    }
}

// lưu vào session
$_SESSION['checkout_items'] = $checkout_items;
$_SESSION['coupon_id'] = $coupon_id;
$_SESSION['discount_amount'] = (int)$discount_amount;

echo json_encode(["success" => true]);