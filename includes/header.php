<?php 
require_once(__DIR__ . "/../config/config.php");

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$currentUser = null;
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;

// ===== LẤY USER =====
if(isset($_SESSION['user_id'])){

    $uid = (int)$_SESSION['user_id'];

    $sql = "SELECT id, username, display_name, avatar, role FROM users WHERE id=?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL Error: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt,"i",$uid);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $currentUser = mysqli_fetch_assoc($result);
}

// ===== NOTIFICATION =====
$notifications = [];
$unreadCount = 0;

if ($currentUser) {

    $uid = (int)$currentUser['id'];

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
            if ($row['is_read'] == 0) $unreadCount++;
        }
    }
}
?>
<?php
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>MilkTea House 5aem</title>


<link rel="stylesheet" href="<?=BASE_URL?>css/base.css">
<link rel="stylesheet" href="<?=BASE_URL?>css/layout.css">

<?php
if(isset($page_css)){
echo '<link rel="stylesheet" href="'.BASE_URL.'css/' . $page_css . '">';
}
?>
<script src="<?= BASE_URL ?>js/script.js"></script>

</head>

<body>

<header class="header">

<div class="logo">
<a href="<?= BASE_URL ?>index.php">MilkTea House 5aem</a>
</div>

<nav class="menu">

<a href="<?= BASE_URL ?>index.php">Trang chủ</a>
<a href="<?= BASE_URL ?>pages/category.php?id=1">Trà sữa</a>
<a href="<?= BASE_URL ?>pages/category.php?id=2">Trà trái cây</a>
<a href="<?= BASE_URL ?>pages/category.php?id=3">Đá xay</a>
<a href="<?= BASE_URL ?>pages/category.php?id=4">Topping</a>

<a href="<?= BASE_URL ?>pages/cart.php" onclick="return checkLogin()">
Giỏ hàng
    (<span id="cart-count"><?= $cartCount ?></span>)
</a>

</nav>

<div class="header-right">

<?php if(!$currentUser): ?>

<!-- Guest -->

<a href="<?= BASE_URL ?>pages/login.php" class="btn">Đăng nhập</a>
<a href="<?= BASE_URL ?>pages/register.php" class="btn">Đăng ký</a>

<?php else: ?>

<!-- Logged user -->

<!-- Notification Bell -->
<div class="noti-wrapper">
<div class="noti-bell" onclick="toggleNotiMenu()">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
    </svg>
    <?php if ($unreadCount > 0): ?>
        <span class="noti-badge"><?= $unreadCount ?></span>
    <?php endif; ?>
</div>

<!-- Dropdown Thông báo -->
<div class="noti-dropdown" id="notiDropdown">
    <div class="noti-header">
        <h3>Thông báo</h3>
        <span class="noti-close" onclick="toggleNotiMenu()">&times;</span>
    </div>
    <div class="noti-list">
        <?php if (empty($notifications)): ?>
            <div class="noti-empty">Không có thông báo nào</div>
        <?php else: ?>
            <?php foreach ($notifications as $noti): ?>
                <a href="<?= BASE_URL . ($noti['link'] ?? '#') ?>" class="noti-item <?= $noti['is_read'] == 0 ? 'unread' : '' ?>">
                    <div class="noti-content">
                        <p class="noti-title"><?= htmlspecialchars($noti['title']) ?></p>
                        <p class="noti-msg"><?= htmlspecialchars($noti['message']) ?></p>
                        <small class="noti-time"><?= date('H:i d/m/Y', strtotime($noti['created_at'])) ?></small>
                    </div>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <div class="noti-footer">
        <a href="#">Xem tất cả</a>
    </div>
</div>
</div>

<div class="user-menu">
<img
src="<?= $currentUser['avatar'] ? BASE_URL.'uploads/'.$currentUser['avatar'] : BASE_URL.'images/user.jpg' ?>"
class="avatar"
onclick="toggleUserMenu()"
>

<div class="user-dropdown" id="userDropdown">

<p class="user-name">
Xin chào, <?= htmlspecialchars($currentUser['display_name'] ?: $currentUser['username']) ?>
</p>

<a href="<?= BASE_URL ?>pages/profile.php">Tài khoản</a>
<a href="<?= BASE_URL ?>pages/orders.php">Đơn hàng</a>

<?php if($currentUser['role'] === 'admin'): ?>
<a href="<?= BASE_URL ?>admin/dashboard.php">Admin Dashboard</a>
<?php endif; ?>

<a href="<?= BASE_URL ?>pages/logout.php">Đăng xuất</a>

</div>

</div>

<?php endif; ?>

</div>

</header>
<script>
 window.BASE_URL = "<?php echo rtrim(BASE_URL, '/'); ?>/";
 window.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; 
 ?>;

function checkLogin() {

  if (!isLoggedIn) {

    alert("Vui lòng đăng nhập để xem giỏ hàng");

    window.location.href = window.BASE_URL + "pages/login.php";

    return false;
  }

  return true;
}

</script>