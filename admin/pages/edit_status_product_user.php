<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

// check login
if (!isset($_SESSION['user_id'])) {
    die("Chưa đăng nhập");
}

// lấy id
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) die("ID không hợp lệ");

// =======================
// LẤY ORDER + USER
// =======================
$order = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT o.*, u.username 
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.id = $id
"));

if (!$order) die("Không tìm thấy đơn hàng");

// =======================
//  LẤY ORDER ITEMS
// =======================
$res_items = mysqli_query($conn, "
    SELECT oi.qty, oi.price, p.name 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.id
    WHERE oi.order_id = $id
");

$items = [];
while ($row = mysqli_fetch_assoc($res_items)) {
    $items[] = $row;
}

// =======================
// UPDATE STATUS
// =======================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $status = $_POST['status'] ?? '';

    $allowed = ['pending','processing','shipping','completed','cancelled'];

    if (!in_array($status, $allowed)) {
        die("Trạng thái không hợp lệ");
    }

    mysqli_query($conn, "
        UPDATE orders 
        SET status = '$status'
        WHERE id = $id
    ");

    header("Location: orders.php");
    exit;
}

// label
$statusLabels = [
    'pending'    => 'Chờ xác nhận',
    'processing' => 'Đang xử lý',
    'shipping'   => 'Đang giao hàng',
    'completed'  => 'Đã hoàn thành',
    'cancelled'  => 'Đã hủy',
];
?>

<link rel="stylesheet" href="../css/admin_products.css">
<div class="dashboard">
    <a class="topbar-link" href="../dashboard.php">🏠 Trang chủ</a>

    <div class="topbar">
        <h1>📦 Chi tiết đơn hàng #<?= $order['id'] ?></h1>
        <a href="orders.php">← Quay lại</a>
    </div>

    <div class="form-box order-detail">

        <!-- 🔥 GRID INFO -->
        <div class="order-info-grid">

            <div class="form-group">
                <label>👤 Khách hàng</label>
                <div><?= htmlspecialchars($order['username']) ?></div>
            </div>

            <div class="form-group">
                <label>📞 SĐT</label>
                <div><?= htmlspecialchars($order['phone']) ?></div>
            </div>

            <div class="form-group full">
                <label>📍 Địa chỉ</label>
                <div><?= htmlspecialchars($order['address']) ?></div>
            </div>

        </div>

        <!-- 🔥 DANH SÁCH SẢN PHẨM -->
        <div class="form-group">
            <label>🧋 Sản phẩm</label>

            <div class="order-items">
                <?php foreach ($items as $item): ?>
                    <div class="order-item">
                        <div>
                            <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                            <div class="item-meta">SL: <?= $item['qty'] ?></div>
                        </div>

                        <div class="item-price">
                            <?= number_format($item['price']) ?>đ
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- 🔥 TỔNG + STATUS -->
        <div class="order-summary">

            <div class="total-box">
                Tổng: <?= number_format($order['total']) ?>đ
            </div>

            <div class="status-badge <?= $order['status'] ?>">
                <?= $statusLabels[$order['status']] ?? $order['status'] ?>
            </div>

        </div>

        <!-- 🔥 UPDATE STATUS -->
        <form method="POST">

            <div class="form-group">
                <label>🔄 Cập nhật trạng thái</label>
                <select name="status">
                    <?php foreach ($statusLabels as $key => $label): ?>
                        <option value="<?= $key ?>" 
                            <?= $order['status'] === $key ? 'selected' : '' ?>>
                            <?= $label ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button class="btn-save">💾 Cập nhật trạng thái</button>

        </form>
    </div>
</div>