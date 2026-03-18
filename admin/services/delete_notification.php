<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../auth_admin.php");

$id = intval($_GET['id'] ?? 0);

if ($id > 0) {
    $sql = "DELETE FROM notifications WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ../pages/notifications.php?status=success&msg=Đã xóa thông báo");
    } else {
        header("Location: ../pages/notifications.php?status=error&msg=Không thể xóa thông báo");
    }
} else {
    header("Location: ../pages/notifications.php");
}
exit;
?>