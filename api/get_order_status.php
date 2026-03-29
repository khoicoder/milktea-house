<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

$ref = $_GET['ref'] ?? '';
if (!$ref) {
    echo json_encode(["success" => false]);
    exit;
}

// 1. Kiểm tra trong bảng chính trước (đề phòng đã được lưu)
$stmt = $conn->prepare("SELECT id FROM orders WHERE payment_method != 'cod' AND status = 'pending' AND (qr_content LIKE ? OR id = ?) LIMIT 1");
$search_ref = "%$ref%";
$stmt->bind_param("ss", $search_ref, $ref);
$stmt->execute();
if ($stmt->get_result()->fetch_assoc()) {
    echo json_encode(["success" => true]);
    exit;
}

// 2. Kiểm tra trong bảng tạm payment_waiting
$stmt = $conn->prepare("SELECT * FROM payment_waiting WHERE reference = ? LIMIT 1");
$stmt->bind_param("s", $ref);
$stmt->execute();
$waiting = $stmt->get_result()->fetch_assoc();

if ($waiting && $waiting['is_paid'] == 1) {
    // Đã thanh toán thành công! Tiến hành lưu vào bảng orders chính thức
    $order = json_decode($waiting['order_data'], true);
    
    mysqli_begin_transaction($conn);
    try {
        $stmtOrder = mysqli_prepare($conn, "INSERT INTO orders (user_id, coupon_id, total, discount_amount, status, name, phone, address, note, payment_method, payment_status, paid_at) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, 'paid', NOW())");
        mysqli_stmt_bind_param($stmtOrder, "iiiissssss", $order['user_id'], $order['coupon_id'], $order['total'], $order['discount_amount'], $order['name'], $order['phone'], $order['address'], $order['note'], $order['payment_method']);
        mysqli_stmt_execute($stmtOrder);
        $order_id = mysqli_insert_id($conn);

        // CỘNG LƯỢT DÙNG MÃ GIẢM GIÁ
        if ($order['coupon_id'] && $order['discount_amount'] > 0) {
            $cid = (int)$order['coupon_id'];
            mysqli_query($conn, "UPDATE coupons SET used_count = used_count + 1 WHERE id = $cid");
        }

        foreach ($order['items'] as $item) {
            mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, price) VALUES ($order_id, {$item['id']}, {$item['qty']}, {$item['price']})");
            mysqli_query($conn, "UPDATE products SET stock = stock - {$item['qty']} WHERE id = {$item['id']}");
        }

        // Xóa khỏi bảng tạm
        mysqli_query($conn, "DELETE FROM payment_waiting WHERE reference = '$ref'");
        
        mysqli_commit($conn);
        echo json_encode(["success" => true, "order_id" => $order_id]);
    } catch (Exception $e) {
        mysqli_rollback($conn);
        echo json_encode(["success" => false, "message" => $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false]);
}
