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

$stock = $product['stock'];

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

$qty = $_SESSION['cart'][$id] ?? 0;

if($stock <= 0){

    sendAdminNotification(
        $conn,
        '⚠️ Sản phẩm hết hàng',
        "Sản phẩm \"{$product['name']}\" (ID: $id) đã hết hàng khi user ID: $user_id thao tác.",
        'admin/pages/products.php'
    );

    echo json_encode([
        "status"=>"error",
        "message"=>"Sản phẩm đã hết hàng"
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
        "message"=>"Bạn đã thêm vượt số lượng tồn kho"
    ]);
    exit;
}

$_SESSION['cart'][$id] = $qty + 1;

$count = array_sum($_SESSION['cart']);

echo json_encode([
    "status"=>"success",
    "message"=>"Đã thêm vào giỏ hàng",
    "cart_count"=>$count
]);
exit;