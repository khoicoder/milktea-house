<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$order_id = (int)($data['order_id'] ?? 0);
$reference = $data['reference'] ?? '';
$user_id = (int)$_SESSION['user_id'];

mysqli_begin_transaction($conn);

try {
    $order = null;
    if ($order_id > 0) {
        $sql = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id LIMIT 1";
        $res = mysqli_query($conn, $sql);
        $order = mysqli_fetch_assoc($res);
    }

    // Nếu không tìm thấy order_id nhưng có reference, thử lấy từ payment_waiting
    if (!$order && $reference) {
        $stmt = $conn->prepare("SELECT * FROM payment_waiting WHERE reference = ? LIMIT 1");
        $stmt->bind_param("s", $reference);
        $stmt->execute();
        $waiting = $stmt->get_result()->fetch_assoc();

        if ($waiting) {
            $order_data = json_decode($waiting['order_data'], true);
            if ($order_data['user_id'] != $user_id) throw new Exception("Yêu cầu không hợp lệ");

            // Di chuyển sang bảng orders chính thức với trạng thái đã thanh toán
            $stmtOrder = mysqli_prepare($conn, "INSERT INTO orders (user_id, coupon_id, total, discount_amount, status, name, phone, address, note, payment_method, payment_status, paid_at, qr_content) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, 'paid', NOW(), ?)");
            // Total 10 parameters to bind (4 integers, 6 strings)
            mysqli_stmt_bind_param($stmtOrder, "iiiissssss", $order_data['user_id'], $order_data['coupon_id'], $order_data['total'], $order_data['discount_amount'], $order_data['name'], $order_data['phone'], $order_data['address'], $order_data['note'], $order_data['payment_method'], $reference);
            mysqli_stmt_execute($stmtOrder);
            $order_id = mysqli_insert_id($conn);

            // Lưu chi tiết đơn hàng
            foreach ($order_data['items'] as $item) {
                mysqli_query($conn, "INSERT INTO order_items (order_id, product_id, qty, price) VALUES ($order_id, {$item['id']}, {$item['quantity']}, {$item['price']})");
                mysqli_query($conn, "UPDATE products SET stock = stock - {$item['quantity']} WHERE id = {$item['id']}");
            }

            // Cập nhật lượt dùng mã giảm giá
            if ($order_data['coupon_id'] && $order_data['discount_amount'] > 0) {
                $cid = (int)$order_data['coupon_id'];
                mysqli_query($conn, "UPDATE coupons SET used_count = used_count + 1 WHERE id = $cid");
            }

            // Xóa khỏi bảng tạm
            mysqli_query($conn, "DELETE FROM payment_waiting WHERE reference = '$reference'");
            
            // Xóa giỏ hàng
            unset($_SESSION['checkout_items']);
            foreach ($order_data['items'] as $item) unset($_SESSION['cart'][$item['id']]);
            unset($_SESSION['coupon_id'], $_SESSION['discount_amount']);

            // Tạo biến order giả lập để dùng tiếp ở dưới
            $order = ['id' => $order_id];
        }
    }

    if (!$order) throw new Exception("Không tìm thấy đơn hàng");
    
    // Nếu đã là đơn hàng trong bảng orders, cập nhật trạng thái nếu chưa paid
    if (isset($order['payment_status']) && $order['payment_status'] !== 'paid') {
        // 1. Cập nhật trạng thái đơn
        mysqli_query($conn, "UPDATE orders SET payment_status = 'paid', status = 'pending', paid_at = NOW() WHERE id = {$order['id']}");

        // 2. Trừ kho thật
        $res_items = mysqli_query($conn, "SELECT product_id, qty FROM order_items WHERE order_id = {$order['id']}");
        while ($item = mysqli_fetch_assoc($res_items)) {
            mysqli_query($conn, "UPDATE products SET stock = stock - {$item['qty']} WHERE id = {$item['product_id']}");
        }

        // 3. Cập nhật lượt dùng mã giảm giá
        if ($order['coupon_id'] && $order['discount_amount'] > 0) {
            $cid = (int)$order['coupon_id'];
            mysqli_query($conn, "UPDATE coupons SET used_count = used_count + 1 WHERE id = $cid");
        }
    }

    // 4. Thông báo
    $oid = $order['id'];
    $title = "Thanh toán thành công";
    $message = "Đơn hàng #$oid của bạn đã thanh toán thành công và đang chờ xác nhận.";
    mysqli_query($conn, "INSERT INTO notifications (user_id, title, message, link) VALUES ($user_id, '$title', '$message', 'pages/order_detail.php?id=$oid')");

    mysqli_commit($conn);
    unset($_SESSION['cart']);

    echo json_encode(["success" => true, "order_id" => $oid]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
