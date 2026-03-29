<?php 
require_once(__DIR__ . "/../config/config.php");
require_once(__DIR__ . "/../cron/expire_orders.php");
require_once(__DIR__ . "/header_logic.php"); ?>

<?php
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>MilkTea House 5aem</title>

<link rel="stylesheet" href="<?= BASE_URL ?>css/base.css">
<link rel="stylesheet" href="<?= BASE_URL ?>css/layout.css">

<?php if(isset($page_css)): ?>
    <link rel="stylesheet" href="<?= BASE_URL ?>css/<?= $page_css ?>">
<?php endif; ?>

<script src="<?= BASE_URL ?>js/script.js"></script>

</head>

<body>

<header class="header">
    <div class="header-container">
        <div class="logo">
            <a href="<?= BASE_URL ?>index.php">MilkTea<span>House</span></a>
        </div>

        <nav class="menu" id="mainMenu">
            <a href="<?= BASE_URL ?>index.php" class="<?= basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : '' ?>">Trang chủ</a>
            <a href="<?= BASE_URL ?>pages/category.php?id=1" class="<?= (isset($_GET['id']) && $_GET['id'] == 1) ? 'active' : '' ?>">Trà sữa</a>
            <a href="<?= BASE_URL ?>pages/category.php?id=2" class="<?= (isset($_GET['id']) && $_GET['id'] == 2) ? 'active' : '' ?>">Trà trái cây</a>
            <a href="<?= BASE_URL ?>pages/category.php?id=3" class="<?= (isset($_GET['id']) && $_GET['id'] == 3) ? 'active' : '' ?>">Đá xay</a>
            <a href="<?= BASE_URL ?>pages/category.php?id=4" class="<?= (isset($_GET['id']) && $_GET['id'] == 4) ? 'active' : '' ?>">Topping</a>
        </nav>

        <div class="header-right">
            <a href="<?= BASE_URL ?>pages/cart.php" class="cart-icon" onclick="return checkLogin()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>
                <span id="cart-count" class="cart-badge"><?= $cartCount ?></span>
            </a>

            <?php if(!$currentUser): ?>
                <div class="auth-btns">
                    <a href="<?= BASE_URL ?>pages/login.php" class="btn btn-outline">Đăng nhập</a>
                    <a href="<?= BASE_URL ?>pages/register.php" class="btn btn-primary">Đăng ký</a>
                </div>
            <?php else: ?>
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
                            <a href="<?= BASE_URL ?>pages/notifications.php">Xem tất cả</a>
                        </div>
                    </div>
                </div>

                <div class="user-menu">
                    <img src="<?= $currentUser['avatar'] ? BASE_URL.'uploads/'.$currentUser['avatar'] : BASE_URL.'images/user.jpg' ?>" class="avatar" onclick="toggleUserMenu()">
                    
                    <div class="user-dropdown" id="userDropdown">
                        <p class="user-name">Xin chào, <?= htmlspecialchars($currentUser['display_name'] ?: $currentUser['username']) ?></p>
                        <a href="<?= BASE_URL ?>pages/profile.php">Tài khoản</a>
                        <a href="<?= BASE_URL ?>pages/orders.php">Đơn hàng</a>

                        <?php if($currentUser['role'] === 'admin'): ?>
                            <a href="<?= BASE_URL ?>admin/dashboard.php">Admin Dashboard</a>
                        <?php endif; ?>

                        <a href="<?= BASE_URL ?>pages/logout.php">Đăng xuất</a>
                    </div>
                </div>
            <?php endif; ?>

            <div class="mobile-toggle" onclick="toggleMobileMenu()">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
            </div>
        </div>
    </div>
</header>

<script>
    window.BASE_URL = "<?= rtrim(BASE_URL, '/'); ?>/";
    window.isLoggedIn = <?= isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;

    function checkLogin() {
        if (!isLoggedIn) {
            alert("Vui lòng đăng nhập để xem giỏ hàng");
            window.location.href = window.BASE_URL + "pages/login.php";
            return false;
        }
        return true;
    }
</script>