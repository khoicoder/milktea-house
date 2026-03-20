
<?php
require_once(__DIR__ . "/../config/config.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

$product_ids = $data['product_ids'] ?? [];

if (empty($product_ids)) {
    echo json_encode(["success" => false]);
    exit;
}

// lưu vào session
$_SESSION['checkout_items'] = array_map('intval', $product_ids);

echo json_encode(["success" => true]);
?>