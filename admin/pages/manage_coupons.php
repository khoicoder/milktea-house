<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$coupons = mysqli_query($conn, "SELECT * FROM coupons ORDER BY id DESC");
?>
<link rel="stylesheet" href="<?= BASE_URL ?>admin/css/admin_manage_products.css">
<style>
    .badge-active { background: #28a745; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-inactive { background: #dc3545; color: #fff; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
</style>

<div class="dashboard">
    <h1>🎫 Quản lý mã giảm giá</h1>
    <a class="topbar-link" href="../dashboard.php">🏠 Trang chủ</a>

    <div style="margin-bottom: 20px;">
        <a href="add_coupon.php" class="topbar-link" style="background: #ff5a5f; color: #fff; padding: 10px 20px; text-decoration: none; border-radius: 8px; font-weight: 600;">➕ Thêm mã mới</a>
    </div>

    <div class="box">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Mã</th>
                    <th>Loại</th>
                    <th>Giá trị</th>
                    <th>Đơn tối thiểu</th>
                    <th>Lượt dùng</th>
                    <th>Trạng thái</th>
                    <th>Hành động</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = mysqli_fetch_assoc($coupons)): ?>
                <tr>
                    <td><?= $c['id'] ?></td>
                    <td style="font-weight: 800; color: #333;"><?= htmlspecialchars($c['code']) ?></td>
                    <td><?= $c['type'] === 'fixed' ? 'Giảm tiền' : 'Giảm %' ?></td>
                    <td><?= $c['type'] === 'fixed' ? number_format($c['value'], 0, ',', '.') . 'đ' : (int)$c['value'] . '%' ?></td>
                    <td><?= number_format($c['min_order_value'], 0, ',', '.') ?>đ</td>
                    <td><?= $c['used_count'] ?> / <?= $c['usage_limit'] ?? '∞' ?></td>
                    <td>
                        <span class="<?= $c['is_active'] ? 'badge-active' : 'badge-inactive' ?>">
                            <?= $c['is_active'] ? 'Hoạt động' : 'Tạm khóa' ?>
                        </span>
                    </td>
                    <td class="actions">
                        <a class="edit" href="edit_coupon.php?id=<?= $c['id'] ?>">✏️</a>
                        <a class="delete" href="../delete_coupon.php?id=<?= $c['id'] ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa mã này?')">🗑</a>
                    </td>
                </tr>
                <?php endwhile; ?>
                <?php if (mysqli_num_rows($coupons) == 0): ?>
                    <tr><td colspan="8" style="text-align: center; padding: 40px; color: #999;">Chưa có mã giảm giá nào.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
