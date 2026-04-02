<?php
// Migration: Thêm product_size_id vào order_items table

return function($conn) {
    // Kiểm tra migration đã được chạy hay chưa
    $check = mysqli_query($conn, "SHOW COLUMNS FROM order_items LIKE 'product_size_id'");
    if (mysqli_num_rows($check) == 0) {
        // Thêm cột product_size_id
        mysqli_query($conn, "
            ALTER TABLE order_items 
            ADD COLUMN product_size_id INT NULL AFTER product_id
        ");
        // Thêm foreign key sau
        mysqli_query($conn, "
            ALTER TABLE order_items 
            ADD CONSTRAINT fk_items_product_size 
            FOREIGN KEY (product_size_id) REFERENCES product_sizes(id) ON DELETE SET NULL
        ");
    }
};
?>
