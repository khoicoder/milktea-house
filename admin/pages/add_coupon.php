<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = strtoupper(mysqli_real_escape_string($conn, $_POST['code']));
    $type = mysqli_real_escape_string($conn, $_POST['type']);
    $value = (float)$_POST['value'];
    $min_order_value = (float)$_POST['min_order_value'];
    $usage_limit = $_POST['usage_limit'] !== '' ? (int)$_POST['usage_limit'] : "NULL";
    $is_active = (int)$_POST['is_active'];

    $sql = "INSERT INTO coupons (code, type, value, min_order_value, usage_limit, is_active) 
            VALUES ('$code', '$type', $value, $min_order_value, $usage_limit, $is_active)";
    
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
        <h1>➕ Thêm mã giảm giá mới</h1>
        <a href="manage_coupons.php">← Quay lại</a>
    </div>

    <?php if(isset($error)): ?>
        <p style="color: red; padding: 10px; background: #fff0f0; border-radius: 8px;"><?= $error ?></p>
    <?php endif; ?>

    <div class="form-box">
        <form method="POST">
            <div class="form-group">
                <label>Mã giảm giá (Ví dụ: GIAM30K)</label>
                <input type="text" name="code" required style="text-transform: uppercase;" placeholder="Nhập mã...">
            </div>

            <div class="form-group">
                <label>Loại giảm giá</label>
                <select name="type" required>
                    <option value="fixed">Giảm tiền mặt (đ)</option>
                    <option value="percentage">Giảm theo phần trăm (%)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Giá trị giảm</label>
                <input type="number" name="value" step="0.01" required placeholder="Ví dụ: 20000 hoặc 15">
            </div>

            <div class="form-group">
                <label>Giá trị đơn hàng tối thiểu (đ)</label>
                <input type="number" name="min_order_value" value="0" required>
            </div>

            <div class="form-group">
                <label>Giới hạn số lần dùng (Để trống nếu không giới hạn)</label>
                <input type="number" name="usage_limit" placeholder="Ví dụ: 50">
            </div>

            <div class="form-group">
                <label>Trạng thái</label>
                <select name="is_active">
                    <option value="1">Kích hoạt</option>
                    <option value="0">Tạm khóa</option>
                </select>
            </div>

            <button type="submit" class="btn-save">💾 Lưu mã giảm giá</button>
        </form>
    </div>
</div>
