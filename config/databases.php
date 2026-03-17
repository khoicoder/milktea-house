<?php
class databases {
    private $servername = "localhost";
    private $username = "root";
    private $password = "";
    private $dbname = "milktea_house";

    public function __construct() {
        // tạo connection
        $conn = new mysqli($this->servername, $this->username, $this->password);

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // tạo database nếu chưa có
        $sql = "CREATE DATABASE IF NOT EXISTS " . $this->dbname;
        $conn->query($sql);

        // chọn database
        $conn->select_db($this->dbname);

        // tạo bảng products
        $sql1 = "CREATE TABLE IF NOT EXISTS products (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            image VARCHAR(255) NOT NULL,
            description TEXT,
            category_id INT(11) NOT NULL,
            stock INT(11) NOT NULL,
            reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        // tạo bảng categories
        $sql2 = "CREATE TABLE IF NOT EXISTS categories (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        // tạo bảng users
        $sql3 = "CREATE TABLE IF NOT EXISTS users (
            id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            display_name VARCHAR(100),
            avatar VARCHAR(255),
            role ENUM('admin', 'customer') DEFAULT 'customer',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        )";

        // chạy từng query
        $conn->query($sql1);
        $conn->query($sql2);
        $conn->query($sql3);

        error_log("Database & tables created successfully");

        $conn->close();
    }
}
?>