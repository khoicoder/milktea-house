<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');
// check login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
;

$product_ids = $data['product_ids'] ?? [];
$name = mysqli_real_escape_string($conn, $data['name'] ?? '');
$phone = mysqli_real_escape_string($conn, $data['phone'] ?? '');
$address = mysqli_real_escape_string($conn, $data['address'] ?? '');
$note = mysqli_real_escape_string($conn, $data['note'] ?? '');

if (empty($product_ids)) {
    echo json_encode(["success" => false, "message" => "Không có sản phẩm"]);
    exit;
}

$user_id = $_SESSION['user_id'];

// ép kiểu chống hack
$safe_ids = array_map('intval', $product_ids);
$ids = implode(',', $safe_ids);

// lấy sản phẩm từ DB
$sql = "SELECT id, price, stock FROM products WHERE id IN ($ids)";
$result = mysqli_query($conn, $sql);

$total = 0;
$valid_items = [];

while ($row = mysqli_fetch_assoc($result)) {
    $pid = $row['id'];
    $qty = $_SESSION['cart'][$pid] ?? 0;

    if ($qty <= 0) continue;

    // check stock
    if ($qty > $row['stock']) {
        echo json_encode([
            "success" => false,
            "message" => "Sản phẩm ID $pid không đủ hàng"
        ]);
        exit;
    }

    $subtotal = $row['price'] * $qty;
    $total += $subtotal;

    $valid_items[] = [
        "id" => $pid,
        "qty" => $qty,
        "price" => $row['price']
    ];
}

if ($total <= 0) {
    echo json_encode(["success" => false, "message" => "Đơn hàng không hợp lệ"]);
    exit;
}

// insert order
$sql_order = "INSERT INTO orders 
(user_id, total, status, created_at, name, phone, address, note)
VALUES 
($user_id, $total, 'pending', NOW(), '$name', '$phone', '$address', '$note')";

if (!mysqli_query($conn, $sql_order)) {
    echo json_encode(["success" => false, "message" => "Lỗi tạo đơn"]);
    exit;
}

$order_id = mysqli_insert_id($conn);

// (OPTIONAL) order_items nếu bạn có bảng
foreach ($valid_items as $item) {
    $pid = $item['id'];
    $qty = $item['qty'];
    $price = $item['price'];

    mysqli_query($conn, "
        INSERT INTO order_items (order_id, product_id, qty, price)
        VALUES ($order_id, $pid, $qty, $price)
    ");

    // trừ kho
    mysqli_query($conn, "
        UPDATE products SET stock = stock - $qty WHERE id = $pid
    ");

    // xóa khỏi cart
    unset($_SESSION['cart'][$pid]);
}

echo json_encode([
    "success" => true,
    "order_id" => $order_id
]);