<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$reference = $data['reference'] ?? '';
$user_id = (int)$_SESSION['user_id'];

if (!$reference) {
    echo json_encode(["success" => false, "message" => "Thiếu mã tham chiếu"]);
    exit;
}

mysqli_begin_transaction($conn);

try {
    // 1. Lấy dữ liệu từ payment_waiting
    $stmt = $conn->prepare("SELECT * FROM payment_waiting WHERE reference = ? LIMIT 1");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $waiting = $stmt->get_result()->fetch_assoc();

    if (!$waiting) throw new Exception("Không tìm thấy thông tin đặt hàng hoặc đơn hàng đã được xử lý");
    
    $order = json_decode($waiting['order_data'], true);
    if ($order['user_id'] != $user_id) throw new Exception("Yêu cầu không hợp lệ");

    // 2. Lưu vào bảng orders chính thức
    $stmtOrder = mysqli_prepare($conn, "INSERT INTO orders (user_id, coupon_id, total, discount_amount, status, name, phone, address, note, payment_method, payment_status, qr_content) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, 'unpaid', ?)");
    // Total 10 parameters to bind (4 integers, 6 strings)
    mysqli_stmt_bind_param($stmtOrder, "iiiissssss", $order['user_id'], $order['coupon_id'], $order['total'], $order['discount_amount'], $order['name'], $order['phone'], $order['address'], $order['note'], $order['payment_method'], $reference);
    mysqli_stmt_execute($stmtOrder);
    $order_id = mysqli_insert_id($conn);

    // 3. Lưu chi tiết đơn hàng & Trừ kho
    foreach ($order['items'] as $item) {
        $product_id = (int)$item['product_id'];
        $product_size_id = (int)$item['product_size_id'];
        $qty = (int)$item['quantity'];
        $price = (int)$item['price'];
        
        mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, product_size_id, qty, price) VALUES ($order_id, $product_id, $product_size_id, $qty, $price)");
        
        // Trừ stock từ product_sizes table
        mysqli_query($conn, "UPDATE product_sizes SET stock = stock - $qty WHERE id = $product_size_id");
    }

    // 4. Cập nhật lượt dùng mã giảm giá
    if ($order['coupon_id'] && $order['discount_amount'] > 0) {
        $cid = (int)$order['coupon_id'];
        mysqli_query($conn, "UPDATE coupons SET used_count = used_count + 1 WHERE id = $cid");
    }

    // 5. Thông báo
    $title = "Đặt hàng thành công";
    $message = "Đơn hàng #$order_id của bạn đã được tiếp nhận (COD).";
    mysqli_query($conn, "INSERT INTO notifications (user_id, title, message, link) VALUES ($user_id, '$title', '$message', 'pages/order_detail.php?id=$order_id')");

    // 6. Xóa khỏi bảng tạm
    mysqli_query($conn, "DELETE FROM payment_waiting WHERE reference = '$reference'");

    // 7. Xóa giỏ hàng và session liên quan
    unset($_SESSION['checkout_items']);
    foreach ($order['items'] as $item) {
        $product_id = (int)($item['product_id'] ?? 0);
        $product_size_id = (int)($item['product_size_id'] ?? 0);
        if ($product_id > 0 && $product_size_id > 0) {
            unset($_SESSION['cart'][$product_id][$product_size_id]);
        }
    }
    unset($_SESSION['coupon_id'], $_SESSION['discount_amount']);

    mysqli_commit($conn);
    echo json_encode(["success" => true, "order_id" => $order_id]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
