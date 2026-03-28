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

if ($order_id <= 0) {
    echo json_encode(["success" => false, "message" => "Mã đơn hàng không hợp lệ"]);
    exit;
}

// Kiểm tra đơn hàng: phải thuộc user và đang ở trạng thái pending
$stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode(["success" => false, "message" => "Không tìm thấy đơn hàng"]);
    exit;
}

if ($order['status'] !== 'pending') {
    echo json_encode(["success" => false, "message" => "Chỉ có thể hủy đơn hàng đang chờ xác nhận"]);
    exit;
}

// Tiến hành hủy
mysqli_begin_transaction($conn);
try {
    // 1. Cập nhật trạng thái
    $stmtUp = $conn->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmtUp->bind_param("i", $order_id);
    $stmtUp->execute();

    // 2. Hoàn lại kho (nếu cần thiết - tùy thuộc vào việc bạn trừ kho lúc nào)
    // Ở bản milktea này, chúng ta trừ kho thật lúc xác nhận thanh toán (pending), 
    // nên khi hủy ở trạng thái pending cần cộng lại kho.
    $res_items = mysqli_query($conn, "SELECT product_id, qty FROM order_items WHERE order_id = $order_id");
    while ($item = mysqli_fetch_assoc($res_items)) {
        mysqli_query($conn, "UPDATE products SET stock = stock + {$item['qty']} WHERE id = {$item['product_id']}");
    }

    mysqli_commit($conn);
    echo json_encode(["success" => true]);
} catch (Exception $e) {
    mysqli_rollback($conn);
    echo json_encode(["success" => false, "message" => "Lỗi hệ thống khi hủy đơn"]);
}
