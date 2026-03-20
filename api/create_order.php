<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception("Chưa đăng nhập");
    }

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    if (!$data || !is_array($data)) {
        throw new Exception("Dữ liệu không hợp lệ");
    }

    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');
    $note = trim($data['note'] ?? '');
    $payment_method = trim($data['payment_method'] ?? 'bank_transfer');

    $user_id = (int)$_SESSION['user_id'];
    $checkout_ids = $_SESSION['checkout_items'] ?? [];

    if (empty($checkout_ids)) {
        throw new Exception("Không có sản phẩm để đặt hàng");
    }

    if ($name === '' || $phone === '' || $address === '') {
        throw new Exception("Thiếu thông tin giao hàng");
    }

    $safe_ids = array_map('intval', $checkout_ids);
    $ids = implode(',', $safe_ids);

    $sql = "SELECT id, price, stock, reserved_stock FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Không lấy được dữ liệu sản phẩm");
    }

    $total = 0;
    $items = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $pid = (int)$row['id'];
        $qty = (int)($_SESSION['cart'][$pid] ?? 0);

        if ($qty <= 0) {
            continue;
        }

        $available = (int)$row['stock'] - (int)$row['reserved_stock'];
        if ($qty > $available) {
            throw new Exception("Sản phẩm ID $pid không đủ hàng khả dụng");
        }

        $subtotal = (int)$row['price'] * $qty;
        $total += $subtotal;

        $items[] = [
            'id' => $pid,
            'qty' => $qty,
            'price' => (int)$row['price']
        ];
    }

    if ($total <= 0 || empty($items)) {
        throw new Exception("Đơn hàng không hợp lệ");
    }

    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    mysqli_begin_transaction($conn);

    $stmt = mysqli_prepare($conn, "
        INSERT INTO orders 
        (user_id, total, status, created_at, name, phone, address, note, payment_method, payment_status, payment_expires_at)
        VALUES (?, ?, 'pending_payment', NOW(), ?, ?, ?, ?, ?, 'unpaid', ?)
    ");

    if (!$stmt) {
        throw new Exception("Không tạo được câu lệnh insert orders");
    }

    mysqli_stmt_bind_param(
        $stmt,
        "iissssss",
        $user_id,
        $total,
        $name,
        $phone,
        $address,
        $note,
        $payment_method,
        $expiresAt
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception("Lỗi tạo đơn");
    }

    $order_id = mysqli_insert_id($conn);

    foreach ($items as $item) {
        $pid = (int)$item['id'];
        $qty = (int)$item['qty'];
        $price = (int)$item['price'];

        $stmtItem = mysqli_prepare($conn, "
            INSERT INTO order_items (order_id, product_id, qty, price)
            VALUES (?, ?, ?, ?)
        ");
        if (!$stmtItem) {
            throw new Exception("Không tạo được order_items");
        }

        mysqli_stmt_bind_param($stmtItem, "iiii", $order_id, $pid, $qty, $price);

        if (!mysqli_stmt_execute($stmtItem)) {
            throw new Exception("Lỗi tạo order items");
        }

        $stmtReserve = mysqli_prepare($conn, "
            UPDATE products
            SET reserved_stock = reserved_stock + ?
            WHERE id = ?
        ");
        if (!$stmtReserve) {
            throw new Exception("Không tạo được câu lệnh giữ hàng");
        }

        mysqli_stmt_bind_param($stmtReserve, "ii", $qty, $pid);

        if (!mysqli_stmt_execute($stmtReserve)) {
            throw new Exception("Lỗi giữ hàng");
        }
    }

    $qr_content = "MILKTEA HOUSE|ORDER:$order_id|USER:$user_id|AMOUNT:$total|ACCOUNT:123456789|BANK:MB BANK|NAME:LE MINH KHOI";

    $stmtQr = mysqli_prepare($conn, "
        UPDATE orders 
        SET qr_content = ? 
        WHERE id = ?
    ");
    if (!$stmtQr) {
        throw new Exception("Không tạo được câu lệnh cập nhật QR");
    }

    mysqli_stmt_bind_param($stmtQr, "si", $qr_content, $order_id);

    if (!mysqli_stmt_execute($stmtQr)) {
        throw new Exception("Lỗi cập nhật QR");
    }

    mysqli_commit($conn);

    unset($_SESSION['checkout_items']);

    echo json_encode([
        "success" => true,
        "order_id" => $order_id,
        "payment_url" => BASE_URL . "pages/payment.php?order_id=" . $order_id
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (isset($conn) && $conn) {
        mysqli_rollback($conn);
    }

    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}