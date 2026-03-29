<?php
return function($conn) {
    // Thêm các cột còn thiếu vào bảng orders
    $columns = [
        'name' => "ALTER TABLE orders ADD COLUMN name VARCHAR(100) AFTER created_at",
        'phone' => "ALTER TABLE orders ADD COLUMN phone VARCHAR(20) AFTER name",
        'address' => "ALTER TABLE orders ADD COLUMN address TEXT AFTER phone",
        'note' => "ALTER TABLE orders ADD COLUMN note TEXT AFTER address",
        'payment_method' => "ALTER TABLE orders ADD COLUMN payment_method VARCHAR(50) DEFAULT 'bank_transfer' AFTER note",
        'payment_status' => "ALTER TABLE orders ADD COLUMN payment_status VARCHAR(20) DEFAULT 'unpaid' AFTER payment_method",
        'payment_expires_at' => "ALTER TABLE orders ADD COLUMN payment_expires_at DATETIME AFTER payment_status",
        'qr_content' => "ALTER TABLE orders ADD COLUMN qr_content TEXT AFTER payment_expires_at"
    ];

    foreach ($columns as $column => $sql) {
        // Kiểm tra xem cột đã tồn tại chưa
        $check = $conn->query("SHOW COLUMNS FROM orders LIKE '$column'");
        if ($check->num_rows == 0) {
            $conn->query($sql);
        }
    }
};
