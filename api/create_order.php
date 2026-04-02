<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_SESSION['user_id'])) throw new Exception("Chưa đăng nhập");

    $raw = file_get_contents("php://input");
    $data = json_decode($raw, true);

    $name = trim($data['name'] ?? '');
    $phone = trim($data['phone'] ?? '');
    $address = trim($data['address'] ?? '');
    $note = trim($data['note'] ?? '');
    $payment_method = trim($data['payment_method'] ?? 'bank_transfer');
    $coupon_id = isset($data['coupon_id']) ? (int)$data['coupon_id'] : null;

    $user_id = (int)$_SESSION['user_id'];
    $checkout_items = $_SESSION['checkout_items'] ?? [];  // Now it's [[$prod_id, $size_id, $qty], ...]

    if (empty($checkout_items)) throw new Exception("Không có sản phẩm để đặt hàng");

    // Tính tổng tiền từ product_sizes table
    $subtotal = 0;
    $items_detail = [];
    foreach ($checkout_items as $item_pair) {
        if (!is_array($item_pair) || count($item_pair) < 2) continue;
        
        $product_id = (int)$item_pair[0];
        $product_size_id = (int)$item_pair[1];
        $qty = (int)($item_pair[2] ?? 1);  // Lấy qty từ checkout_items
        
        // Lấy thông tin từ product_sizes
        $res_size = mysqli_query($conn, "
            SELECT ps.price, ps.id, p.name 
            FROM product_sizes ps
            JOIN products p ON ps.product_id = p.id
            WHERE ps.id = $product_size_id AND ps.product_id = $product_id
        ");
        $size_info = mysqli_fetch_assoc($res_size);
        
        if ($size_info) {
            $price = (int)$size_info['price'];
            $subtotal += $price * $qty;
            $items_detail[] = [
                'product_id' => $product_id,
                'product_size_id' => $product_size_id,
                'name' => $size_info['name'],
                'quantity' => $qty,
                'price' => $price
            ];
        }
    }

    $discount_amount = 0;
    if ($coupon_id) {
        $res_c = mysqli_query($conn, "SELECT * FROM coupons WHERE id = $coupon_id AND is_active = 1");
        $coupon = mysqli_fetch_assoc($res_c);
        if ($coupon && $subtotal >= $coupon['min_order_value']) {
            $discount_amount = ($coupon['type'] === 'fixed') ? $coupon['value'] : ($subtotal * $coupon['value'] / 100);
        }
    }

    $total = $subtotal - $discount_amount;

    // TẤT CẢ PHƯƠNG THỨC: Lưu vào bảng tạm payment_waiting
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
        'items' => $items_detail
    ];

    $json_data = json_encode($order_data, JSON_UNESCAPED_UNICODE);
    $stmt = mysqli_prepare($conn, "INSERT INTO payment_waiting (reference, order_data) VALUES (?, ?)");
    mysqli_stmt_bind_param($stmt, "ss", $reference, $json_data);
    mysqli_stmt_execute($stmt);

    echo json_encode(["success" => true, "reference" => $reference]);

} catch (Exception $e) {
    if (isset($conn) && $conn) mysqli_rollback($conn);
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}