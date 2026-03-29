<?php
require_once(__DIR__ . "/../config/config.php");

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];

// 1. Đánh dấu tất cả thông báo là đã đọc khi vào trang này
mysqli_query($conn, "UPDATE notifications SET is_read = 1 WHERE user_id = $uid OR user_id IS NULL");

// 2. Lấy danh sách tất cả thông báo (Thông báo riêng + Thông báo chung)
$sql = "SELECT * FROM notifications 
        WHERE user_id = $uid OR user_id IS NULL 
        ORDER BY created_at DESC";
$res = mysqli_query($conn, $sql);
$all_notifications = [];
while ($row = mysqli_fetch_assoc($res)) {
    $all_notifications[] = $row;
}

$page_title = "Thông báo của tôi";
$page_css = "notifications.css";
include(__DIR__ . "/../includes/header.php");
?>

<main class="noti-page-container">
    <div class="noti-card">
        <div class="noti-page-header">
            <h2>Tất cả thông báo</h2>
            <span style="font-size: 13px; color: #888;"><?= count($all_notifications) ?> thông báo</span>
        </div>

        <div class="noti-full-list">
            <?php if (empty($all_notifications)): ?>
                <div class="noti-page-empty">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <p>Bạn chưa có thông báo nào.</p>
                </div>
            <?php else: ?>
                <?php foreach ($all_notifications as $noti): ?>
                    <a href="<?= $noti['link'] ? BASE_URL . $noti['link'] : 'javascript:void(0)' ?>" 
                       class="noti-full-item <?= $noti['user_id'] === null ? 'is-public' : '' ?>">
                        <div class="noti-full-title">
                            <?= htmlspecialchars($noti['title']) ?>
                            <?php if ($noti['user_id'] === null): ?>
                                <span class="badge-public">Tin chung</span>
                            <?php endif; ?>
                        </div>
                        <div class="noti-full-msg"><?= htmlspecialchars($noti['message']) ?></div>
                        <div class="noti-full-time">
                            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 4px; vertical-align: middle;">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                            <?= date('H:i - d/m/Y', strtotime($noti['created_at'])) ?>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include(__DIR__ . "/../includes/footer.php"); ?>
