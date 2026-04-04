<?php

require_once("../config/config.php");
require_once(__DIR__ . "/../admin/services/notification_helper.php");
header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])){
    echo json_encode([
        "status"=>"error",
        "message"=>"Vui lòng đăng nhập để thêm vào giỏ hàng"
    ]);
    exit;
}
$user_id = $_SESSION['user_id'];
$id = intval($_POST['id'] ?? 0);
$product_size_id = intval($_POST['product_size_id'] ?? 0);

// Kiểm tra product_size_id có tồn tại không
if ($product_size_id <= 0) {
    echo json_encode([
        "status"=>"error",
        "message"=>"Vui lòng chọn size trước khi thêm vào giỏ hàng"
    ]);
    exit;
}

// Lấy thông tin product_sizes (bao gồm giá, stock)
$size_result = mysqli_query($conn, "SELECT ps.*, p.stock as product_stock FROM product_sizes ps 
                                     JOIN products p ON ps.product_id = p.id
                                     WHERE ps.id=$product_size_id AND ps.product_id=$id");
$size_info = mysqli_fetch_assoc($size_result);

if (!$size_info) {
    echo json_encode([
        "status"=>"error",
        "message"=>"Size không tồn tại"
    ]);
    exit;
}

$sql = "SELECT * FROM products WHERE id=$id";
$result = mysqli_query($conn,$sql);
$product = mysqli_fetch_assoc($result);

if(!$product){
    echo json_encode([
        "status"=>"error",
        "message"=>"Sản phẩm không tồn tại"
    ]);
    exit;
}

$stock = intval($size_info['stock']);

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}
// Structure mới: cart[$id][$product_size_id] = quantity
// Nếu key tồn tại nhưng là scalar (old structure), chuyển thành array
if(!isset($_SESSION['cart'][$id]) || !is_array($_SESSION['cart'][$id])){
    $_SESSION['cart'][$id] = [];
}

$qty_in_cart = $_SESSION['cart'][$id][$product_size_id] ?? 0;

// 1. Kiểm tra nếu sản phẩm đã hết sạch trong kho
if($stock <= 0){
    sendAdminNotification(
        $conn,
        '⚠️ Sản phẩm hết hàng',
        "Sản phẩm \"{$product['name']}\" (ID: $id) đã hết hàng khi người dùng ID: $user_id cố gắng thêm vào giỏ.",
        'admin/pages/manage_products.php'
    );

    echo json_encode([
        "status"=>"error",
        "message"=>"Sản phẩm hiện đang hết hàng"
    ]);
    exit;
}
if($stock <= 3){
     echo json_encode([
        "status"=>"error",
        "message"=>"sắp hết hàng, vui lòng liên hệ admin để được hỗ trợ"
    ]);
    exit;
}

if($qty_in_cart + 1 > $stock){

    sendAdminNotification(
        $conn,
        '⚠️ Thiếu hàng',
        "User ID: $user_id đang cố mua vượt tồn kho sản phẩm \"{$product['name']}\"",
        'admin/pages/products.php',
        $id
    );

    echo json_encode([
        "status"=>"error",
        "message"=>"Sản phẩm này chỉ còn $stock sản phẩm, bạn đã thêm tối đa số lượng cho phép"
    ]);
    exit;
}

// 3. Thêm vào giỏ hàng
$_SESSION['cart'][$id][$product_size_id] = (int)($qty_in_cart + 1);

// Tính tổng item trong giỏ (cộng tất cả quantity từ tất cả size)
$count = 0;
if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
    foreach($_SESSION['cart'] as $product_id => $product_sizes) {
        if (is_array($product_sizes)) {
            foreach ($product_sizes as $qty) {
                $count += (int)$qty;
            }
        }
    }
}
$product_qty = array_sum($_SESSION['cart'][$id] ?? []);
echo json_encode([
    "status"=>"success",
    "message"=>"Đã thêm vào giỏ hàng",
    "cart_count"=>$count,
    "product_qty" => $product_qty
]);
exit;