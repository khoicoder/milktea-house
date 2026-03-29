<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$id = (int)$_GET['id'];
$coupon = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM coupons WHERE id = $id"));

if (!$coupon) {
    header("Location: manage_coupons.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = strtoupper(mysqli_real_escape_string($conn, $_POST['code']));
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $value = (float)$_POST['value'];
    $min_order_value = (float)$_POST['min_order_value'];
    $usage_limit = $_POST['usage_limit'] !== '' ? (int)$_POST['usage_limit'] : "NULL";
    $is_active = (int)$_POST['is_active'];

    $sql = "UPDATE coupons SET 
            code = '$code', 
            type = '$type', 
            value = $value, 
            min_order_value = $min_order_value, 
            usage_limit = $usage_limit, 
            is_active = $is_active 
            WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        header("Location: manage_coupons.php");
        exit;
    } else {
        $error = "Lỗi: " . mysqli_error($conn);
    }
}
?>
<link rel="stylesheet" href="../css/admin_products.css">

<div class="dashboard">
    <a class="topbar-link" href="../dashboard.php">🏠 Trang chủ</a>

    <div class="topbar">
        <h1>✏️ Chỉnh sửa mã giảm giá</h1>
        <a href="manage_coupons.php">← Quay lại</a>
    </div>

    <?php if(isset($error)): ?>
        <p style="color: red; padding: 10px; background: #fff0f0; border-radius: 8px;"><?= $error ?></p>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST">
            <div class="form-group">
                <label>Mã giảm giá</label>
                <input type="text" name="code" value="<?= htmlspecialchars($coupon['code']) ?>" required style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label>Loại giảm giá</label>
                <select name="type" required>
                    <option value="fixed" <?= $coupon['type'] === 'fixed' ? 'selected' : '' ?>>Giảm tiền mặt (đ)</option>
                    <option value="percentage" <?= $coupon['type'] === 'percentage' ? 'selected' : '' ?>>Giảm theo phần trăm (%)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Giá trị giảm</label>
                <input type="number" name="value" value="<?= $coupon['value'] ?>" step="0.01" required>
            </div>

            <div class="form-group">
                <label>Giá trị đơn hàng tối thiểu (đ)</label>
                <input type="number" name="min_order_value" value="<?= $coupon['min_order_value'] ?>" required>
            </div>

            <div class="form-group">
                <label>Giới hạn số lần dùng</label>
                <input type="number" name="usage_limit" value="<?= $coupon['usage_limit'] ?>" placeholder="Không giới hạn">
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="is_active">
                    <option value="1" <?= $coupon['is_active'] == 1 ? 'selected' : '' ?>>Kích hoạt</option>
                    <option value="0" <?= $coupon['is_active'] == 0 ? 'selected' : '' ?>>Tạm khóa</option>
                </select>
            </div>

            <button type="submit" class="btn-save">💾 Cập nhật mã giảm giá</button>
        </form>
    </div>
</div>
