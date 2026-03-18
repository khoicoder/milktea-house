<?php
return function($conn) {

    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // USERS
    $conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE,
        email VARCHAR(120) UNIQUE,
        password VARCHAR(255),
        role ENUM('admin','user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // CATEGORIES
    $conn->query("
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // PRODUCTS
    $conn->query("
    CREATE TABLE IF NOT EXISTS products (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100),
        price DECIMAL(12,0),
        image VARCHAR(255),
        description TEXT,
        category_id INT,
        stock INT DEFAULT 10,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    // ORDERS (✅ FK ở đây là đủ)
    $conn->query("
    CREATE TABLE IF NOT EXISTS orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        total DECIMAL(12,0),
        status ENUM('pending','processing','completed') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_orders_user 
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // ORDER ITEMS (✅ thêm FK luôn)
    $conn->query("
    CREATE TABLE IF NOT EXISTS order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        order_id INT,
        product_id INT,
        qty INT,
        price DECIMAL(12,0),

        CONSTRAINT fk_items_order 
        FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,

        CONSTRAINT fk_items_product 
        FOREIGN KEY (product_id) REFERENCES products(id)
    )");

    // ADMIN LOGS
    $conn->query("
    CREATE TABLE IF NOT EXISTS admin_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT,
        action VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");

    $conn->query("SET FOREIGN_KEY_CHECKS=1");
};