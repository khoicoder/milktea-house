<?php
return function($conn) {

    // ================= USERS =================
    $check = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc();

    if ($check['total'] == 0) {

        $conn->query("
        INSERT INTO users (id, username, email, password, role, created_at) VALUES
        (1, 'admin', 'admin@gmail.com', '$2y$10$LUgGOk8ana2/TXLCcpkMuuUlzpHnmTZkOWqtWS2KmRPLWQPFZIroy', 'admin', NOW()),
        (2, 'khoi', 'khoi@gmail.com', '123', 'user', NOW()),
        (3, 'an', 'an@gmail.com', '123', 'user', NOW()),
        (4, 'minh', 'minh@gmail.com', '123', 'user', NOW()),
        (5, 'linh', 'linh@gmail.com', '123', 'user', NOW()),
        (6, 'hung', 'hung@gmail.com', '123', 'user', NOW())
        ");
    }

    // ================= CATEGORIES =================
    $conn->query("
    INSERT INTO categories (id, name) VALUES
    (1, 'Trà sữa'),
    (2, 'Trà trái cây'),
    (3, 'Đá xay'),
    (4, 'Topping')
    ON DUPLICATE KEY UPDATE name=VALUES(name)
    ");

    // ================= PRODUCTS =================
    $conn->query("
    INSERT INTO products (id, name, price, image, description, category_id, stock) VALUES
    (1, 'Trà sữa truyền thống', 30000, 'tra-sua-truyen-thong.jpg', 'Traditional milk tea', 1, 9),
    (2, 'Trà sữa khoai môn', 35000, 'tra-sua-khoai-mon.jpg', 'Sweet taro flavor', 1, 8),
    (3, 'Trà sữa matcha', 38000, 'tra-sua-matcha.jpg', 'Japanese matcha milk tea', 1, 10),
    (4, 'Trà sữa socola', 40000, 'tra-sua-socola.jpg', 'Chocolate milk tea', 1, 10),
    (5, 'Trà sữa caramel', 42000, 'tra-sua-caramel.jpg', 'Sweet caramel milk tea', 1, 9),
    (6, 'Trà sữa ô long', 39000, 'tra-sua-o-long.jpg', 'Oolong milk tea', 1, 10),
    (7, 'Trà sữa hoa nhài', 37000, 'tra-sua-hoa-nhai.jpg', 'Jasmine milk tea', 1, 10),
    (8, 'Trà sữa trân châu', 45000, 'tra-sua-tran-chau.jpg', 'Milk tea with pearls', 1, 7),

    (9, 'Trà đào', 28000, 'tra-dao.jpg', 'Fresh peach tea', 2, 0),
    (10, 'Trà xoài', 30000, 'tra-xoai.jpg', 'Sweet mango tea', 2, 10),
    (11, 'Trà dâu', 30000, 'tra-dau.jpg', 'Strawberry tea', 2, 10),
    (12, 'Trà vải', 30000, 'tra-vai.jpg', 'Lychee tea', 2, 10),
    (13, 'Trà chanh', 25000, 'tra-chanh.jpg', 'Lemon tea', 2, 10),
    (14, 'Trà chanh dây', 32000, 'tra-chanh-day.jpg', 'Passion fruit tea', 2, 10),
    (15, 'Trà việt quất', 35000, 'tra-viet-quat.jpg', 'Blueberry tea', 2, 10),

    (16, 'Matcha đá xay', 45000, 'matcha-da-xay.jpg', 'Blended matcha', 3, 9),
    (17, 'Socola đá xay', 45000, 'socola-da-xay.jpg', 'Blended chocolate', 3, 10),
    (18, 'Oreo đá xay', 48000, 'oreo-da-xay.jpg', 'Oreo blended', 3, 7),
    (19, 'Dâu đá xay', 43000, 'dau-da-xay.jpg', 'Strawberry blended', 3, 10),
    (20, 'Xoài đá xay', 44000, 'xoai-da-xay.jpg', 'Mango blended', 3, 10),

    (21, 'Trân châu đen', 10000, 'tran-chau-den.jpg', 'Black pearl', 4, 99),
    (22, 'Trân châu trắng', 10000, 'tran-chau-trang.jpg', 'White pearl', 4, 100),
    (23, 'Pudding trứng', 12000, 'pudding-trung.jpg', 'Egg pudding', 4, 99),
    (24, 'Thạch dừa', 9000, 'thach-dua.jpg', 'Coconut jelly', 4, 100),
    (25, 'Nha đam', 9000, 'nha-dam.jpg', 'Aloe vera', 4, 98)

    ON DUPLICATE KEY UPDATE name=VALUES(name)
    ");

    // ================= ORDERS =================
    $conn->query("
    INSERT INTO orders (id, user_id, total, status, created_at) VALUES
    (1,2,100000,'completed','2026-03-10'),
    (2,3,42000,'completed','2026-03-11'),
    (3,4,90000,'completed','2026-03-11'),
    (4,2,55000,'processing','2026-03-12'),
    (5,5,96000,'completed','2026-03-13'),
    (6,6,56000,'completed','2026-03-14'),
    (7,3,65000,'completed','2026-03-15'),
    (8,2,38000,'completed','2026-03-15'),
    (9,4,88000,'completed','2026-03-16')
    ON DUPLICATE KEY UPDATE total=VALUES(total)
    ");

    // ================= ORDER ITEMS =================
    $conn->query("
    INSERT INTO order_items (order_id, product_id, qty, price) VALUES
    (1,8,2,45000),
    (1,21,1,10000),
    (2,1,1,30000),
    (2,23,1,12000),
    (3,16,2,45000),
    (4,2,1,35000),
    (4,21,2,10000),
    (5,18,2,48000),
    (6,9,2,28000),
    (7,8,1,45000),
    (7,22,2,10000),
    (8,3,1,38000),
    (9,20,2,44000)
    ");
};