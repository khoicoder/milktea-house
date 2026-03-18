<?php
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../auth_admin.php");

// --- TỰ ĐỘNG TẠO BẢNG NẾU CHƯA CÓ ---
$create_table_sql = "CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT 0,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
mysqli_query($conn, $create_table_sql);
// ------------------------------------

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = intval($_POST['user_id'] ?? 0); 
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $link = mysqli_real_escape_string($conn, $_POST['link'] ?? '');

    if (empty($title) || empty($message)) {
        header("Location: ../pages/notifications.php?status=error&msg=Vui lòng nhập đủ tiêu đề và nội dung");
        exit;
    }

    $sql = "INSERT INTO notifications (user_id, title, message, link) VALUES (?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "isss", $user_id, $title, $message, $link);
        if (mysqli_stmt_execute($stmt)) {
            header("Location: ../pages/notifications.php?status=success&msg=Gửi thông báo thành công");
        } else {
            $err = mysqli_error($conn);
            header("Location: ../pages/notifications.php?status=error&msg=Lỗi thực thi: $err");
        }
    } else {
        $err = mysqli_error($conn);
        die("Lỗi nghiêm trọng: Không thể tạo hoặc truy cập bảng thông báo. Lỗi: $err");
    }
    exit;
}
?>