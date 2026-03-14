<?php
// $_SESSION['cart'] = [];

require_once("../config/config.php");

header('Content-Type: application/json');

$id = $_POST['id'] ?? 0;

$sql = "SELECT * FROM products WHERE id=$id";
$result = mysqli_query($conn,$sql);
$product = mysqli_fetch_assoc($result);

if(!isset($_SESSION['user_id'])){
    echo json_encode([
        "status"=>"error",
        "message"=>"Vui lòng đăng nhập để thêm vào giỏ hàng",
        
    ]);
    header("Location: " . BASE_URL . "pages/login.php");
    
    exit;
}



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
    echo json_encode([
        "status"=>"error",
        "message"=>"Sản phẩm đã hết hàng"
    ]);
    exit;
}

if($qty + 1 > $stock){
    echo json_encode([
        "status"=>"error",
        "message"=>"Bạn đã thêm vượt số lượng"
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
session_start();

$id = $_POST['id'];

if(!isset($_SESSION['cart'])){
    $_SESSION['cart'] = [];
}

if(isset($_SESSION['cart'][$id])){
    $_SESSION['cart'][$id]++;
}else{
    $_SESSION['cart'][$id] = 1;
}

echo array_sum($_SESSION['cart']);