<?php
session_start();
require_once(__DIR__ . "/databases.php");

// Tự động nhận diện giao thức (Xử lý trường hợp ngrok/proxy dùng HTTPS)
if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
    $protocol = "https://";
} else {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
}

$host = $_SERVER['HTTP_HOST'];

// Cách lấy thư mục gốc của project chính xác hơn:
// Lấy đường dẫn của file hiện tại (config/config.php), sau đó lấy thư mục cha của nó.
$project_root_physical = dirname(__DIR__); 
$doc_root = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
$project_root_physical = str_replace('\\', '/', $project_root_physical);

// Tính toán phần dư giữa DOCUMENT_ROOT và thư mục dự án để ra URL path
$project_path = str_replace($doc_root, '', $project_root_physical);
$project_path = '/' . ltrim($project_path, '/') . '/';
$project_path = str_replace('//', '/', $project_path);

$base_url = $protocol . $host . $project_path;

define("BASE_URL", $base_url);
define("UPLOAD_PATH", BASE_URL . "uploads/");

// tạo biến $conn global
$db = new Database();
$conn = $db->conn; 

// Link chạy migration: BASE_URL + config/migrate.php
?>