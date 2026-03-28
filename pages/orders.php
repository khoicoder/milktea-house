<?php
require_once(__DIR__ . "/../config/config.php");

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Xây dựng câu truy vấn lấy đơn hàng
$sql_orders = "SELECT * FROM orders WHERE user_id = ?";
if ($status_filter !== 'all') {
    $sql_orders .= " AND status = ?";
}
$sql_orders .= " ORDER BY created_at DESC";

$stmt_orders = mysqli_prepare($conn, $sql_orders);
if ($status_filter !== 'all') {
    mysqli_stmt_bind_param($stmt_orders, "is", $uid, $status_filter);
} else {
    mysqli_stmt_bind_param($stmt_orders, "i", $uid);
}
mysqli_stmt_execute($stmt_orders);
$orders_res = mysqli_stmt_get_result($stmt_orders);

$orders = [];
while ($row = mysqli_fetch_assoc($orders_res)) {
    $order_id = $row['id'];
    $sql_items = "SELECT oi.*, p.name, p.image 
                  FROM order_items oi 
                  JOIN products p ON oi.product_id = p.id 
                  WHERE oi.order_id = ?";
    $stmt_items = mysqli_prepare($conn, $sql_items);
    mysqli_stmt_bind_param($stmt_items, "i", $order_id);
    mysqli_stmt_execute($stmt_items);
    $items_res = mysqli_stmt_get_result($stmt_items);
    
    $items = [];
    while ($item = mysqli_fetch_assoc($items_res)) {
        $items[] = $item;
    }
    
    $row['items'] = $items;
    $orders[] = $row;
}

$page_css = "orders.css";
include(__DIR__ . "/../includes/header.php");
?>

<main class="orders-page">
    <div class="orders-container">
        <header class="orders-header">
            <h1 class="orders-title">Đơn hàng của tôi</h1>
            
            <div class="status-filter">
                <a href="?status=all" class="filter-btn <?= $status_filter === 'all' ? 'active' : '' ?>">Tất cả</a>
                <a href="?status=pending" class="filter-btn <?= $status_filter === 'pending' ? 'active' : '' ?>">Chờ xác nhận</a>
                <a href="?status=processing" class="filter-btn <?= $status_filter === 'processing' ? 'active' : '' ?>">Đang xử lý</a>
                <a href="?status=shipped" class="filter-btn <?= $status_filter === 'shipped' ? 'active' : '' ?>">Đang giao</a>
                <a href="?status=completed" class="filter-btn <?= $status_filter === 'completed' ? 'active' : '' ?>">Đã hoàn thành</a>
                <a href="?status=cancelled" class="filter-btn <?= $status_filter === 'cancelled' ? 'active' : '' ?>">Đã hủy</a>
            </div>
        </header>

        <div class="orders-list">
            <?php if (empty($orders)): ?>
                <div class="empty-orders">
                    <div class="empty-icon">📦</div>
                    <p class="empty-text">Bạn chưa có đơn hàng nào.</p>
                    <a href="<?= BASE_URL ?>index.php" class="btn btn-primary">Mua sắm ngay</a>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <div class="info-item">
                                    <span class="info-label">Mã đơn hàng</span>
                                    <span class="info-value">#<?= $order['id'] ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Ngày đặt</span>
                                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
                                </div>
                            </div>
                            <div class="order-status">
                                <span class="badge status-<?= $order['status'] ?>">
                                    <?php
                                    switch($order['status']) {
                                        case 'pending': echo 'Chờ xác nhận'; break;
                                        case 'pending_payment': echo 'Chờ thanh toán'; break;
                                        case 'processing': echo 'Đang xử lý'; break;
                                        case 'shipped': echo 'Đang giao'; break;
                                        case 'completed': echo 'Đã hoàn thành'; break;
                                        case 'cancelled': echo 'Đã hủy'; break;
                                        default: echo $order['status'];
                                    }
                                    ?>
                                </span>
                            </div>
                        </div>

                        <div class="order-body">
                            <div class="order-items-list">
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item">
                                        <img src="<?= BASE_URL ?>images/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-img">
                                        <div class="item-details">
                                            <h4 class="item-name"><?= htmlspecialchars($item['name']) ?></h4>
                                            <p class="item-meta">Số lượng: <?= $item['qty'] ?></p>
                                        </div>
                                        <div class="item-price">
                                            <?= number_format($item['price'], 0, ',', '.') ?>đ
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <div class="order-footer">
                            <div class="order-total">
                                <span class="order-total-label">Tổng cộng:</span>
                                <span class="order-total-value"><?= number_format($order['total'], 0, ',', '.') ?>đ</span>
                            </div>
                            <div class="order-actions">
                                <!-- Nút hủy đơn: Chỉ hiện khi trạng thái là pending -->
                                <?php if ($order['status'] === 'pending'): ?>
                                    <button class="btn btn-outline btn-sm" style="color: #ff5a5f; border-color: #ff5a5f;" onclick="cancelOrder(<?= $order['id'] ?>)">Hủy đơn</button>
                                <?php endif; ?>

                                <?php if ($order['status'] === 'completed' || $order['status'] === 'cancelled'): ?>
                                    <button class="btn btn-outline btn-sm" onclick="reorder(<?= $order['id'] ?>)">Mua lại</button>
                                <?php endif; ?>
                                
                                <a href="<?= BASE_URL ?>pages/order_detail.php?id=<?= $order['id'] ?>" class="btn btn-primary btn-sm">Chi tiết</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</main>

<script>
function cancelOrder(orderId) {
    if (confirm("Bạn có chắc chắn muốn hủy đơn hàng #" + orderId + " không?")) {
        fetch("<?= BASE_URL ?>api/cancel_order.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ order_id: orderId })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Đã hủy đơn hàng thành công!");
                location.reload();
            } else {
                alert(data.message || "Không thể hủy đơn hàng lúc này.");
            }
        })
        .catch(() => alert("Lỗi kết nối server"));
    }
}

function reorder(orderId) {
    alert("Tính năng Mua lại đơn hàng #" + orderId + " đang được phát triển!");
}
</script>

<?php include(__DIR__ . "/../includes/footer.php"); ?>
