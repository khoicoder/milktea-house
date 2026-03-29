<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập"]);
    exit;
}

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

$code = strtoupper(trim($data['code'] ?? ''));
$total = (int)($data['total'] ?? 0);

if ($code === '') {
    echo json_encode(["success" => false, "message" => "Vui lòng nhập mã"]);
    exit;
}

$stmt = mysqli_prepare($conn, "SELECT * FROM coupons WHERE code = ? AND is_active = 1 LIMIT 1");
mysqli_stmt_bind_param($stmt, "s", $code);
mysqli_stmt_execute($stmt);
$coupon = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$coupon) {
    echo json_encode(["success" => false, "message" => "Mã giảm giá không tồn tại hoặc đã hết hạn"]);
    exit;
}

// KIỂM TRA: Một tài khoản chỉ được dùng mã này 1 lần
$uid = (int)$_SESSION['user_id'];
$coupon_id = $coupon['id'];
$stmt_check_used = mysqli_prepare($conn, "SELECT id FROM orders WHERE user_id = ? AND coupon_id = ? AND status != 'cancelled' LIMIT 1");
mysqli_stmt_bind_param($stmt_check_used, "ii", $uid, $coupon_id);
mysqli_stmt_execute($stmt_check_used);
$already_used = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_check_used));

if ($already_used) {
    echo json_encode(["success" => false, "message" => "Bạn đã sử dụng mã giảm giá này cho một đơn hàng trước đó"]);
    exit;
}

// Kiểm tra thời hạn
$now = date('Y-m-d H:i:s');
if (($coupon['start_date'] && $now < $coupon['start_date']) || ($coupon['end_date'] && $now > $coupon['end_date'])) {
    echo json_encode(["success" => false, "message" => "Mã giảm giá đã hết hạn sử dụng"]);
    exit;
}

// Kiểm tra lượt dùng
if ($coupon['usage_limit'] !== null && $coupon['used_count'] >= $coupon['usage_limit']) {
    echo json_encode(["success" => false, "message" => "Mã giảm giá đã hết lượt sử dụng"]);
    exit;
}

// Kiểm tra đơn hàng tối thiểu
if ($total < $coupon['min_order_value']) {
    echo json_encode(["success" => false, "message" => "Đơn hàng tối thiểu " . number_format($coupon['min_order_value'], 0, ',', '.') . "đ để áp dụng"]);
    exit;
}

// Tính toán giảm giá
$discount = 0;
if ($coupon['type'] === 'fixed') {
    $discount = $coupon['value'];
} else {
    $discount = ($total * $coupon['value']) / 100;
}

// Giảm giá không vượt quá tổng đơn
$discount = min($discount, $total);

echo json_encode([
    "success" => true,
    "coupon_id" => $coupon['id'],
    "code" => $coupon['code'],
    "discount_amount" => $discount,
    "message" => "Áp dụng mã giảm giá thành công!"
], JSON_UNESCAPED_UNICODE);
