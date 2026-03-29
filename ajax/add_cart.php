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

$stock = intval($product['stock']);

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

$qty_in_cart = $_SESSION['cart'][$id] ?? 0;

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

if($qty + 1 > $stock){

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
$_SESSION['cart'][$id] = $qty_in_cart + 1;

$count = array_sum($_SESSION['cart']);

echo json_encode([
    "status"=>"success",
    "message"=>"Đã thêm vào giỏ hàng",
    "cart_count"=>$count
]);
exit;