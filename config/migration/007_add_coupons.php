<?php
return function($conn) {
    // 1. Tạo bảng coupons
    $conn->query("
    CREATE TABLE IF NOT EXISTS coupons (
        id INT AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(20) UNIQUE NOT NULL,
        type ENUM('fixed', 'percentage') NOT NULL, -- Giảm tiền mặt hoặc %
        value DECIMAL(10,2) NOT NULL,
        min_order_value DECIMAL(12,0) DEFAULT 0, -- Giá trị đơn hàng tối thiểu để áp dụng
        usage_limit INT DEFAULT NULL, -- Số lần sử dụng tối đa
        used_count INT DEFAULT 0,
        start_date DATETIME NULL,
        end_date DATETIME NULL,
        is_active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Thêm cột vào bảng orders để lưu thông tin giảm giá
    $check_coupon_id = $conn->query("SHOW COLUMNS FROM orders LIKE 'coupon_id'");
    if ($check_coupon_id->num_rows == 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN coupon_id INT NULL AFTER user_id");
    }

    $check_discount = $conn->query("SHOW COLUMNS FROM orders LIKE 'discount_amount'");
    if ($check_discount->num_rows == 0) {
        $conn->query("ALTER TABLE orders ADD COLUMN discount_amount DECIMAL(12,0) DEFAULT 0 AFTER total");
    }

    // 3. Seed một số mã mẫu 
    //code
    // loại(fixed(tiền cụ thể, percentage(phần trăm))
    // value(Nếu là fixed thì nhập số tiền, nếu là percentage thì nhập số từ 1-100)
    // min_order_value: Đơn hàng phải đạt bao nhiêu tiền thì mới áp dụng được mã này.
    // usage_limit: Tổng số lượt mã này có thể dùng (ví dụ: chỉ 50 khách hàng đầu tiên được dùng).
    // is_active: 1 là mã đang hoạt động, 0 là mã đã bị vô hiệu hóa.
    $conn->query("INSERT IGNORE INTO coupons (code, type, value, min_order_value, usage_limit, is_active) VALUES 
        ('CHAOBAN', 'fixed', 10000, 50000, 100, 1),
        ('MILKTEA20', 'percentage', 20, 100000, 50, 1),
        ('FREE5K', 'fixed', 5000, 0, 999, 1)
    ");
};
