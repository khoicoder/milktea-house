<?php
return function($conn) {
 
    $conn->query("ALTER TABLE users MODIFY COLUMN phone TEXT");
    $conn->query("ALTER TABLE users MODIFY COLUMN address TEXT");

    // Nếu các cột khác chưa có thì thêm vào (phòng trường hợp migration cũ chưa chạy hết)
    $cols = [
        'display_name' => "VARCHAR(100) AFTER username",
        'avatar' => "VARCHAR(255) AFTER display_name"
    ];

    foreach ($cols as $col => $def) {
        $check = $conn->query("SHOW COLUMNS FROM users LIKE '$col'");
        if ($check->num_rows == 0) {
            $conn->query("ALTER TABLE users ADD COLUMN $col $def");
        }
    }
};
