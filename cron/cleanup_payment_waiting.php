<?php
/**
 * Script này dùng để dọn dẹp các yêu cầu thanh toán bị bỏ dở trong bảng payment_waiting
 * (Khách hàng tạo thanh toán nhưng không bấm "Đã thanh toán" hoặc không thanh toán thành công)
 */
require_once(__DIR__ . "/../config/config.php");

// Xóa các bản ghi đã tạo quá 24 giờ
$sql = "DELETE FROM payment_waiting WHERE created_at < NOW() - INTERVAL 24 HOUR";

if (mysqli_query($conn, $sql)) {
    $deleted_count = mysqli_affected_rows($conn);
    if ($deleted_count > 0) {
        error_log("Cleanup: Đã xóa $deleted_count yêu cầu thanh toán cũ trong payment_waiting.");
    }
} else {
    error_log("Cleanup error: " . mysqli_error($conn));
}
