<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$product_ids = $data['product_ids'] ?? [];
$coupon_id = $data['coupon_id'] ?? null;
$discount_amount = $data['discount_amount'] ?? 0;

if (empty($product_ids)) {
    echo json_encode(["success" => false, "message" => "Vui lòng chọn sản phẩm"]);
    exit;
}

// lưu vào session
$_SESSION['checkout_items'] = array_map('intval', $product_ids);
$_SESSION['coupon_id'] = $coupon_id;
$_SESSION['discount_amount'] = (int)$discount_amount;

echo json_encode(["success" => true]);
