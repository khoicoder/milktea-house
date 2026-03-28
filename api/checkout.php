<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Vui lòng đăng nhập để tiếp tục"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$product_ids = $data['product_ids'] ?? [];
$name = mysqli_real_escape_string($conn, $data['name'] ?? '');
$phone = mysqli_real_escape_string($conn, $data['phone'] ?? '');
$address = mysqli_real_escape_string($conn, $data['address'] ?? '');
$note = mysqli_real_escape_string($conn, $data['note'] ?? '');

if (empty($product_ids)) {
    echo json_encode(["success" => false, "message" => "Giỏ hàng trống hoặc không có sản phẩm được chọn"]);
    exit;
}

$user_id = $_SESSION['user_id'];
$safe_ids = array_map('intval', $product_ids);
$ids_str = implode(',', $safe_ids);

// Bắt đầu transaction
mysqli_begin_transaction($conn);

try {
    // 1. Lấy và khóa hàng (FOR UPDATE) để tránh race condition
    $sql = "SELECT id, name, price, stock FROM products WHERE id IN ($ids_str) FOR UPDATE";
    $result = mysqli_query($conn, $sql);
    
    $total = 0;
    $valid_items = [];
    $products_in_db = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products_in_db[$row['id']] = $row;
    }

    foreach ($safe_ids as $pid) {
        $qty = $_SESSION['cart'][$pid] ?? 0;
        if ($qty <= 0) continue;

        if (!isset($products_in_db[$pid])) {
            throw new Exception("Sản phẩm ID $pid không tồn tại");
        }

        $p_data = $products_in_db[$pid];
        if ($qty > $p_data['stock']) {
            throw new Exception("Sản phẩm \"{$p_data['name']}\" không đủ hàng (Còn: {$p_data['stock']})");
        }

        $subtotal = $p_data['price'] * $qty;
        $total += $subtotal;

        $valid_items[] = [
            "id" => $pid,
            "qty" => $qty,
            "price" => $p_data['price']
        ];
    }

    if ($total <= 0) {
        throw new Exception("Đơn hàng không có sản phẩm hợp lệ");
    }

    // 2. Tạo đơn hàng
    $sql_order = "INSERT INTO orders 
    (user_id, total, status, created_at, name, phone, address, note)
    VALUES 
($user_id, $total, 'pending', NOW(), '$name', '$phone', '$address', '$note')";

    if (!mysqli_query($conn, $sql_order)) {
        throw new Exception("Lỗi khi lưu đơn hàng");
    }

    $order_id = mysqli_insert_id($conn);

    // 3. Lưu chi tiết và trừ kho
    foreach ($valid_items as $item) {
        $pid = $item['id'];
        $qty = $item['qty'];
        $price = $item['price'];

        $sql_item = "INSERT INTO order_items (order_id, product_id, qty, price) VALUES ($order_id, $pid, $qty, $price)";
        if (!mysqli_query($conn, $sql_item)) {
            throw new Exception("Lỗi khi lưu chi tiết đơn hàng");
        }

        $sql_update_stock = "UPDATE products SET stock = stock - $qty WHERE id = $pid";
        if (!mysqli_query($conn, $sql_update_stock)) {
            throw new Exception("Lỗi khi cập nhật tồn kho");
        }

        // Xóa khỏi cart session sau khi đặt thành công
        unset($_SESSION['cart'][$pid]);
    }

    mysqli_commit($conn);

    echo json_encode([
        "success" => true,
        "message" => "Đặt hàng thành công",
        "order_id" => $order_id
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}