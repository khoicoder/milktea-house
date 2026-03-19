<?php
return function($conn) {
    // Thêm các cột còn thiếu vào bảng users để hỗ trợ trang Profile
    $columns = [
        'display_name' => "ALTER TABLE users ADD COLUMN display_name VARCHAR(100) AFTER username",
        'avatar' => "ALTER TABLE users ADD COLUMN avatar VARCHAR(255) AFTER display_name",
        'phone' => "ALTER TABLE users ADD COLUMN phone VARCHAR(20) AFTER email",
        'address' => "ALTER TABLE users ADD COLUMN address TEXT AFTER phone"
    ];

    foreach ($columns as $column => $sql) {
        // Kiểm tra xem cột đã tồn tại chưa
        $check = $conn->query("SHOW COLUMNS FROM users LIKE '$column'");
        if ($check->num_rows == 0) {
            $conn->query($sql);
        }
    }
};
