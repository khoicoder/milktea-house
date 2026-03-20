<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn, "SELECT id, username, role FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Nếu không tồn tại user hoặc không phải admin
if (!$user || $user['role'] !== 'admin') {
    // Destroy session cho chắc
    session_destroy();
    header("Location: ../index.php");
    exit;
}
$timeout = 1800; // 30 phút

if (isset($_SESSION['last_active']) && time() - $_SESSION['last_active'] > $timeout) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

$_SESSION['last_active'] = time();

  
if (!isset($_SESSION['ip'])) {
    $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
}

if ($_SESSION['ip'] !== $_SERVER['REMOTE_ADDR']) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}


// (Optional) dùng lại thông tin admin
$currentAdmin = $user;
?>