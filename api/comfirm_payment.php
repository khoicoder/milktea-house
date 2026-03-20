
<!-- chức năng này:

kiểm tra đơn thuộc user

kiểm tra chưa hết hạn

đổi trạng thái sang paid

trừ kho thật

giảm reserved_stock

gửi thông báo -->

<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "message" => "Chưa đăng nhập"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$order_id = (int)($data['order_id'] ?? 0);
$user_id = (int)$_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id LIMIT 1";
$res = mysqli_query($conn, $sql);
$order = mysqli_fetch_assoc($res);

if (!$order) {
    echo json_encode(["success" => false, "message" => "Không tìm thấy đơn hàng"]);
    exit;
}

if ($order['payment_status'] !== 'unpaid') {
    echo json_encode(["success" => false, "message" => "Đơn này đã được xử lý"]);
    exit;
}

if (strtotime($order['payment_expires_at']) < time()) {
    echo json_encode(["success" => false, "message" => "Đơn đã quá hạn thanh toán"]);
    exit;
}

mysqli_begin_transaction($conn);

try {
    $itemsSql = "SELECT product_id, qty FROM order_items WHERE order_id = $order_id";
    $itemsRes = mysqli_query($conn, $itemsSql);

    while ($item = mysqli_fetch_assoc($itemsRes)) {
        $pid = (int)$item['product_id'];
        $qty = (int)$item['qty'];

        $stmt = mysqli_prepare($conn, "
            UPDATE products
            SET stock = stock - ?, reserved_stock = reserved_stock - ?
            WHERE id = ?
        ");
        mysqli_stmt_bind_param($stmt, "iii", $qty, $qty, $pid);
        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception("Lỗi cập nhật kho");
        }
    }

    $stmtOrder = mysqli_prepare($conn, "
        UPDATE orders
        SET payment_status = 'paid',
            status = 'processing',
            paid_at = NOW()
        WHERE id = ?
    ");
    mysqli_stmt_bind_param($stmtOrder, "i", $order_id);
    if (!mysqli_stmt_execute($stmtOrder)) {
        throw new Exception("Lỗi cập nhật đơn");
    }

    $title = "Thanh toán thành công";
    $message = "Đơn hàng #$order_id của bạn đã được thanh toán.";
    $link = "pages/orders.php";

    $stmtNoti = mysqli_prepare($conn, "
        INSERT INTO notifications (user_id, title, message, link)
        VALUES (?, ?, ?, ?)
    ");
    mysqli_stmt_bind_param($stmtNoti, "isss", $user_id, $title, $message, $link);
    if (!mysqli_stmt_execute($stmtNoti)) {
        throw new Exception("Lỗi tạo thông báo");
    }

    mysqli_commit($conn);

    echo json_encode(["success" => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}