<?php
require_once("databases.php");

$db = new Database();
$conn = $db->conn;

// tạo bảng migrations
$conn->query("
CREATE TABLE IF NOT EXISTS migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255)
)");

// lấy danh sách migration đã chạy
$ran = [];
$result = $conn->query("SELECT name FROM migrations");

while ($row = $result->fetch_assoc()) {
    $ran[] = $row['name'];
}

// lấy file migration
$files = glob(__DIR__ . "/migration/*.php");

sort($files); // đảm bảo chạy đúng thứ tự 001 → 002

foreach ($files as $file) {

    $name = basename($file);

    if (!in_array($name, $ran)) {

        echo "Running: $name <br>";

        $migration = require $file;
        $migration($conn);

        $conn->query("INSERT INTO migrations(name) VALUES('$name')");
    }
}

echo " Migration done!";