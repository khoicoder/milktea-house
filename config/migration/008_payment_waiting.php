<?php
return function($conn) {
    // Tạo bảng tạm để chờ thanh toán, tránh làm rác bảng orders
    $conn->query("
    CREATE TABLE IF NOT EXISTS payment_waiting (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reference VARCHAR(50) UNIQUE NOT NULL, -- Nội dung chuyển khoản (ORDER...)
        order_data TEXT NOT NULL,              -- JSON chứa toàn bộ thông tin đơn hàng
        is_paid TINYINT(1) DEFAULT 0,          -- Đã nhận được tiền chưa
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
};
