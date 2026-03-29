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
    $coupon_id = isset($data['coupon_id']) ? (int)$data['coupon_id'] : null;

    if ($name === '' || $phone === '' || $address === '') {
        throw new Exception("Thiếu thông tin giao hàng");
    }

    $user_id = (int)$_SESSION['user_id'];
    $checkout_ids = $_SESSION['checkout_items'] ?? [];

    if (empty($checkout_ids)) {
        throw new Exception("Không có sản phẩm để đặt hàng");
    }

    $safe_ids = array_map('intval', $checkout_ids);
    $ids = implode(',', $safe_ids);

    $sql = "SELECT id, price, stock, reserved_stock, name FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);

    if (!$result) {
        throw new Exception("Không lấy được dữ liệu sản phẩm");
    }

    $subtotal = 0;
    $items = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $pid = (int)$row['id'];
        $qty = (int)($_SESSION['cart'][$pid] ?? 0);

        if ($qty <= 0) continue;

        $available = (int)$row['stock'] - (int)$row['reserved_stock'];
        if ($qty > $available) {
            throw new Exception("Sản phẩm ID $pid không đủ hàng");
        }

        $subtotal += (int)$row['price'] * $qty;

        $items[] = [
            'id' => $pid,
            'name' => $row['name'],
            'qty' => $qty,
            'price' => (int)$row['price']
        ];
    }

    if ($subtotal <= 0 || empty($items)) {
        throw new Exception("Đơn hàng không hợp lệ");
    }

    // ===== COUPON =====
    $discount_amount = 0;
    if ($coupon_id) {
        $res_c = mysqli_query($conn, "SELECT * FROM coupons WHERE id = $coupon_id AND is_active = 1");
        $coupon = mysqli_fetch_assoc($res_c);

        if ($coupon && $subtotal >= $coupon['min_order_value']) {
            $discount_amount = ($coupon['type'] === 'fixed')
                ? $coupon['value']
                : ($subtotal * $coupon['value'] / 100);
        }
    }

    $total = $subtotal - $discount_amount;

    // =========================================================
    // 🔥 CASE 1: CHUYỂN KHOẢN → LƯU payment_waiting
    // =========================================================
    if ($payment_method === 'bank_transfer') {

        $reference = "REF" . time() . rand(10, 99);

        $order_data = [
            'user_id' => $user_id,
            'coupon_id' => $coupon_id,
            'total' => $total,
            'discount_amount' => $discount_amount,
            'name' => $name,
            'phone' => $phone,
            'address' => $address,
            'note' => $note,
            'payment_method' => $payment_method,
            'items' => $items
        ];

        $json_data = json_encode($order_data, JSON_UNESCAPED_UNICODE);

        $stmt = mysqli_prepare($conn,
            "INSERT INTO payment_waiting (reference, order_data) VALUES (?, ?)"
        );

        mysqli_stmt_bind_param($stmt, "ss", $reference, $json_data);
        mysqli_stmt_execute($stmt);

        echo json_encode([
            "success" => true,
            "type" => "waiting_payment",
            "reference" => $reference
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }

    // =========================================================
    // 🔥 CASE 2: COD → TẠO ORDER + GIỮ HÀNG
    // =========================================================
    mysqli_begin_transaction($conn);

    $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $stmt = mysqli_prepare($conn, "
        INSERT INTO orders (
            user_id, total, discount_amount, coupon_id,
            status, name, phone, address, note,
            payment_method, payment_status, payment_expires_at
        ) VALUES (?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, 'pending', ?)
    ");

    mysqli_stmt_bind_param(
        $stmt,
        "iiiissssss",
        $user_id,
        $total,
        $discount_amount,
        $coupon_id,
        $name,
        $phone,
        $address,
        $note,
        $payment_method,
        $expiresAt
    );

    mysqli_stmt_execute($stmt);
    $order_id = mysqli_insert_id($conn);

    foreach ($items as $item) {
        $pid = $item['id'];
        $qty = $item['qty'];
        $price = $item['price'];

        $stmtItem = mysqli_prepare($conn, "
            INSERT INTO order_items (order_id, product_id, qty, price)
            VALUES (?, ?, ?, ?)
        ");

        mysqli_stmt_bind_param($stmtItem, "iiii", $order_id, $pid, $qty, $price);
        mysqli_stmt_execute($stmtItem);

        $stmtReserve = mysqli_prepare($conn, "
            UPDATE products
            SET reserved_stock = reserved_stock + ?
            WHERE id = ?
        ");

        mysqli_stmt_bind_param($stmtReserve, "ii", $qty, $pid);
        mysqli_stmt_execute($stmtReserve);
    }

    // QR
    $qr_content = "MILKTEA HOUSE|ORDER:$order_id|AMOUNT:$total";

    $stmtQr = mysqli_prepare($conn, "
        UPDATE orders SET qr_content = ? WHERE id = ?
    ");

    mysqli_stmt_bind_param($stmtQr, "si", $qr_content, $order_id);
    mysqli_stmt_execute($stmtQr);

    mysqli_commit($conn);

    unset($_SESSION['checkout_items']);

    echo json_encode([
        "success" => true,
        "type" => "created_order",
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