<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kiểm tra quyền: nếu không phải admin thì mới đá ra, 
// nhưng chúng ta sẽ trim() thật kỹ để tránh lỗi khoảng trắng.
$current_role = isset($_SESSION['role']) ? trim(strtolower($_SESSION['role'])) : '';

if ($current_role !== 'admin') {
    // Nếu không phải admin, thay vì header ngay, hãy thử in ra lỗi để debug
    // header("Location: ../index.php"); 
    // exit;
    die("Bạn không có quyền Admin. Quyền hiện tại của bạn là: '" . $current_role . "'");
}
?>