<?php
class databases {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "milktea_house";

    public function __construct() {
        $conn = new mysqli($this->servername, $this->username, $this->password);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Tạo DB
        $conn->query("CREATE DATABASE IF NOT EXISTS $this->dbname");
        $conn->select_db($this->dbname);

        // Tắt FK để tạo bảng
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

        // ORDERS
        $conn->query("
        CREATE TABLE IF NOT EXISTS orders (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT,
            total DECIMAL(12,0),
            status ENUM('pending','processing','completed') DEFAULT 'pending',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // ORDER ITEMS
        $conn->query("
        CREATE TABLE IF NOT EXISTS order_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            order_id INT,
            product_id INT,
            qty INT,
            price DECIMAL(12,0)
        )");

        // ADMIN LOGS
        $conn->query("
        CREATE TABLE IF NOT EXISTS admin_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            admin_id INT,
            action VARCHAR(255),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");

        // ADD FOREIGN KEY
        $conn->query("ALTER TABLE orders 
            ADD CONSTRAINT fk_orders_user 
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE");

        $conn->query("ALTER TABLE order_items 
            ADD CONSTRAINT fk_items_order 
            FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE");

        $conn->query("ALTER TABLE order_items 
            ADD CONSTRAINT fk_items_product 
            FOREIGN KEY (product_id) REFERENCES products(id)");

        // Bật lại FK
        $conn->query("SET FOREIGN_KEY_CHECKS=1");

        // ======================
        // SEED DATA (nếu trống)
        // ======================
        $check = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc();

        if ($check['total'] == 0) {

            // USERS
            $conn->query("
            INSERT INTO users(username,email,password,role) VALUES
            ('admin','admin@gmail.com','123','admin'),
            ('khoi','khoi@gmail.com','123','user'),
            ('an','an@gmail.com','123','user')
            ");

            // CATEGORIES
            $conn->query("
            INSERT INTO categories(name) VALUES
            ('Trà sữa'),('Trà trái cây'),('Đá xay'),('Topping')
            ");

            // PRODUCTS
            $conn->query("
            INSERT INTO products(name,price,category_id,stock) VALUES
            ('Trà sữa trân châu',45000,1,10),
            ('Trà đào',28000,2,10),
            ('Matcha đá xay',45000,3,10),
            ('Trân châu đen',10000,4,100)
            ");

            // ORDERS
            $conn->query("
            INSERT INTO orders(user_id,total,status) VALUES
            (2,90000,'completed'),
            (3,45000,'completed')
            ");

            // ORDER ITEMS
            $conn->query("
            INSERT INTO order_items(order_id,product_id,qty,price) VALUES
            (1,1,2,45000),
            (2,3,1,45000)
            ");
        }

        echo "DB initialized successfully. 5 aem ";
        $conn->close();
    }
}
?>