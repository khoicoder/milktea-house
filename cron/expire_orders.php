<!-- chạy server-side (cron job) để tự động đổi trạng
  thái đơn hàng đã quá hạn thanh toán sang expired,
   trả lại reserved_stock và gửi thông báo cho khách hàng -->

<?php
require_once(__DIR__ . "/../config/config.php");

$sql = "
    SELECT * 
    FROM orders 
    WHERE payment_status = 'pending'
      AND expired_at IS NULL
      AND payment_expires_at IS NOT NULL
      AND payment_expires_at < NOW()
";

$res = mysqli_query($conn, $sql);

while ($order = mysqli_fetch_assoc($res)) {
    $order_id = (int)$order['id'];
    $user_id = (int)$order['user_id'];  


    mysqli_begin_transaction($conn);
    error_log("Expired order: " . $order_id);

    try {
        $itemsSql = "SELECT product_id, qty FROM order_items WHERE order_id = $order_id";
        $itemsRes = mysqli_query($conn, $itemsSql);

        while ($item = mysqli_fetch_assoc($itemsRes)) {
            $pid = (int)$item['product_id'];
            $qty = (int)$item['qty'];

            mysqli_query($conn, "
                UPDATE products
                SET stock = stock + $qty
                WHERE id = $pid
            ");
        }

        mysqli_query($conn, "
            UPDATE orders
            SET payment_status = 'expired',
                status = 'cancelled',
                expired_at = NOW()
            WHERE id = $order_id
        ");

        $title = "Đơn hàng đã hết hạn";
        $message = "Đơn hàng #$order_id chưa được thanh toán trong 1 giờ và đã bị hủy.";
        $link = "pages/orders.php";

        mysqli_query($conn, "
            INSERT INTO notifications (user_id, title, message, link)
            VALUES ($user_id, '$title', '$message', '$link')
        ");

        mysqli_commit($conn);
    } catch (Exception $e) {
        mysqli_rollback($conn);
    }
}