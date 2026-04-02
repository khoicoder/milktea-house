<?php
require_once(__DIR__ . "/../config/config.php");

// Bắt buộc đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit;
}

$uid = (int)$_SESSION['user_id'];
$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($order_id <= 0) {
    die("Mã đơn hàng không hợp lệ.");
}

// 1. Lấy thông tin đơn hàng
$sql_order = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt_order = mysqli_prepare($conn, $sql_order);

if (!$stmt_order) {
    die("Lỗi truy vấn cơ sở dữ liệu: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt_order, "ii", $order_id, $uid);
mysqli_stmt_execute($stmt_order);
$result_order = mysqli_stmt_get_result($stmt_order);
$order_data = mysqli_fetch_assoc($result_order);

// Kiểm tra nếu không tìm thấy đơn hàng
if (!$order_data) {
    die("Không tìm thấy đơn hàng #$order_id của bạn. Có thể đơn hàng không tồn tại hoặc bạn không có quyền xem.");
}

// 2. Lấy chi tiết sản phẩm
$items = [];
$sql_items = "SELECT oi.*, p.name, p.image, s.name as size_name 
              FROM order_items oi 
              JOIN products p ON oi.product_id = p.id 
              LEFT JOIN product_sizes ps ON oi.product_size_id = ps.id 
              LEFT JOIN sizes s ON ps.size_id = s.id 
              WHERE oi.order_id = ?";
$stmt_items = mysqli_prepare($conn, $sql_items);
if ($stmt_items) {
    mysqli_stmt_bind_param($stmt_items, "i", $order_id);
    mysqli_stmt_execute($stmt_items);
    $items_res = mysqli_stmt_get_result($stmt_items);
    while ($item = mysqli_fetch_assoc($items_res)) {
        $items[] = $item;
    }
}

$page_css = "orders.css";
include(__DIR__ . "/../includes/header.php");
?>

<main class="orders-page">
    <div class="orders-container">
        <header class="orders-header">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <a href="<?= BASE_URL ?>pages/orders.php" class="btn-back">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    Quay lại
                </a>
                <h1 class="orders-title" style="margin-bottom: 0;">Chi tiết đơn hàng #<?= $order_data['id'] ?></h1>
            </div>
            <p style="color: #888;">Đặt ngày: <?= date('d/m/Y H:i', strtotime($order_data['created_at'])) ?></p>
        </header>

        <div style="display: grid; grid-template-columns: 1fr 350px; gap: 30px;">
            <!-- Cột trái: Danh sách sản phẩm -->
            <div>
                <div class="order-card" style="padding: 0;">
                    <div class="order-header" style="background: #fff; border-bottom: 1px solid #eee;">
                        <h3 style="font-size: 16px; margin: 0;">Sản phẩm</h3>
                    </div>
                    <div class="order-body" style="padding: 20px;">
                        <div class="order-items-list">
                            <?php foreach ($items as $item): ?>
                                <div class="order-item" style="padding: 15px 0; display: flex; align-items: center; gap: 15px; border-bottom: 1px solid #f5f5f5;">
                                    <img src="<?= BASE_URL ?>images/<?= $item['image'] ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-img" style="width: 80px; height: 80px; border-radius: 8px; object-fit: cover;">
                                    <div class="item-details" style="flex: 1;">
                                        <h4 class="item-name" style="font-size: 16px; margin-bottom: 5px;"><?= htmlspecialchars($item['name']) ?></h4>
                                        <?php if ($item['size_name']): ?>
                                            <p class="item-meta" style="color: #888; font-size: 13px;">Size: <strong><?= htmlspecialchars($item['size_name']) ?></strong></p>
                                        <?php endif; ?>
                                        <p class="item-meta" style="color: #888; font-size: 13px;">Số lượng: <?= $item['qty'] ?></p>
                                        <p class="item-price" style="color: var(--primary-color); font-weight: 700; margin-top: 5px;"><?= number_format($item['price'], 0, ',', '.') ?>đ</p>
                                    </div>
                                    <div style="font-weight: 800; color: var(--secondary-color);">
                                        <?= number_format($item['price'] * $item['qty'], 0, ',', '.') ?>đ
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="order-footer" style="background: #fafafa; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <span class="order-total-label" style="font-weight: 600;">Tổng tiền hàng:</span>
                        <span class="order-total-value" style="font-size: 20px; font-weight: 800; color: var(--primary-color);"><?= number_format($order_data['total'], 0, ',', '.') ?>đ</span>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Thông tin giao hàng & Thanh toán -->
            <div style="display: flex; flex-direction: column; gap: 20px;">
                <!-- Trạng thái đơn hàng -->
                <div class="order-card" style="padding: 20px; background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 16px; margin-bottom: 15px; color: var(--secondary-color);">Trạng thái</h3>
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <span class="badge status-<?= $order_data['status'] ?>" style="padding: 8px 16px; border-radius: 50px; font-weight: 700; font-size: 13px; background: #eee;">
                            <?php
                            switch($order_data['status']) {
                                case 'pending': echo 'Chờ xác nhận'; break;
                                case 'pending_payment': echo 'Chờ thanh toán'; break;
                                case 'processing': echo 'Đang xử lý'; break;
                                case 'shipping': echo 'Đang giao'; break;
                                case 'completed': echo 'Đã hoàn thành'; break;
                                case 'cancelled': echo 'Đã hủy'; break;
                                default: echo $order_data['status'];
                            }
                            ?>
                        </span>
                        <?php if ($order_data['status'] === 'pending_payment'): ?>
                            <a href="<?= BASE_URL ?>pages/payment.php?order_id=<?= $order_data['id'] ?>" class="btn btn-primary btn-sm">Thanh toán</a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Thông tin người nhận -->
                <div class="order-card" style="padding: 20px; background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 16px; margin-bottom: 15px; color: var(--secondary-color);">Thông tin nhận hàng</h3>
                    <div style="font-size: 14px; line-height: 1.6;">
                        <p style="margin-bottom: 8px;"><strong>Người nhận:</strong> <?= htmlspecialchars($order_data['name'] ?? 'Chưa cập nhật') ?></p>
                        <p style="margin-bottom: 8px;"><strong>Số điện thoại:</strong> <?= htmlspecialchars($order_data['phone'] ?? 'Chưa cập nhật') ?></p>
                        <p style="margin-bottom: 8px;"><strong>Địa chỉ:</strong> <?= htmlspecialchars($order_data['address'] ?? 'Chưa cập nhật') ?></p>
                        <?php if (!empty($order_data['note'])): ?>
                            <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-radius: 8px; font-style: italic;">
                                <strong>Ghi chú:</strong> <?= htmlspecialchars($order_data['note']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Phương thức thanh toán -->
                <div class="order-card" style="padding: 20px; background: #fff; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
                    <h3 style="font-size: 16px; margin-bottom: 15px; color: var(--secondary-color);">Thanh toán</h3>
                    <div style="font-size: 14px; line-height: 1.6;">
                        <p style="margin-bottom: 8px;"><strong>Phương thức:</strong> 
                            <?php 
                                if(($order_data['payment_method'] ?? '') === 'bank_transfer') echo 'Chuyển khoản ngân hàng';
                                elseif(($order_data['payment_method'] ?? '') === 'fake_paypal') echo 'PayPal giả lập';
                                else echo 'Tiền mặt (COD)';
                            ?>
                        </p>
                        
                        <?php if (in_array($order_data['payment_method'], ['bank_transfer', 'fake_paypal']) && !empty($order_data['qr_content'])): ?>
                            <p style="margin-bottom: 8px;"><strong>Nội dung:</strong> <span style="background: #fff; padding: 2px 8px; border: 1px solid #eee; font-weight: bold; color: #000; border-radius: 4px;">ORDER <?= htmlspecialchars($order_data['qr_content']) ?></span></p>
                        <?php endif; ?>

                        <p><strong>Trạng thái:</strong> <?php
                    if ($order_data['status'] === 'completed') {
                            echo '<span style="color: #28a745; font-weight: 700;">Đã thanh toán</span>';
} else {
    echo ($order_data['payment_status'] ?? '') === 'paid'
        ? '<span style="color: #28a745; font-weight: 700;">Đã thanh toán</span>'
        : '<span style="color: #dc3545; font-weight: 700;">Chưa thanh toán</span>';
}
?>
</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include(__DIR__ . "/../includes/footer.php"); ?>
