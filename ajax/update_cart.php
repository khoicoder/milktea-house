<?php
require_once("../config/config.php");
header('Content-Type: application/json');

$id = intval($_POST['id'] ?? 0);
$qty = intval($_POST['quantity'] ?? 1);

if (isset($_SESSION['cart'][$id])) {
    if ($qty > 0) {
        // Kiểm tra tồn kho trước khi cập nhật
        $sql = "SELECT stock FROM products WHERE id = $id";
        $result = mysqli_query($conn, $sql);
        $product = mysqli_fetch_assoc($result);

        if ($product && $qty > $product['stock']) {
            echo json_encode([
                "status" => "error",
                "message" => "Sản phẩm này chỉ còn " . $product['stock'] . " sản phẩm.",
                "max_stock" => $product['stock']
            ]);
            exit;
        }

        $_SESSION['cart'][$id] = $qty; // Cập nhật lại số lượng mới
    } else {
        unset($_SESSION['cart'][$id]);
    }
}

// Đếm lại tổng số lượng trong giỏ để cập nhật Header
$count = empty($_SESSION['cart']) ? 0 : array_sum($_SESSION['cart']);

echo json_encode([
    "status" => "success",
    "cart_count" => $count
]);