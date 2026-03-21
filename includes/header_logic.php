<?php
require_once(__DIR__ . "/../config/config.php");


$currentUser = null;
$cartCount = 0;
$notifications = [];
$unreadCount = 0;

// Tính tổng số lượng sản phẩm trong giỏ hàng
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cartCount = array_sum($_SESSION['cart']);
}

// Kiểm tra user đăng nhập và lấy thông tin
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];

    // Lấy thông tin user (Đã gộp phần bị lặp)
    $stmt = mysqli_prepare($conn, "SELECT id, username, display_name, avatar, role FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt, "i", $uid);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $currentUser = mysqli_fetch_assoc($result);

    // Lấy thông báo (nếu user tồn tại)
    if ($currentUser) {
        $sql_noti = "SELECT * FROM notifications 
                     WHERE user_id = ? OR user_id = 0 
                     ORDER BY created_at DESC LIMIT 10";
        $stmt_noti = mysqli_prepare($conn, $sql_noti);

        if ($stmt_noti) {
            mysqli_stmt_bind_param($stmt_noti, "i", $uid);
            mysqli_stmt_execute($stmt_noti);
            $res_noti = mysqli_stmt_get_result($stmt_noti);

            while ($row = mysqli_fetch_assoc($res_noti)) {
                $notifications[] = $row;
                if ($row['is_read'] == 0) {
                    $unreadCount++;
                }
            }
        }
    }
}
?>