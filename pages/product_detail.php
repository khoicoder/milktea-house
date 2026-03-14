<?php
require_once("../config/config.php");
if(isset($_GET['id'])){

$id = (int)$_GET['id'];

$sql = "SELECT * FROM products WHERE id=$id";
$result = mysqli_query($conn,$sql);
$product = mysqli_fetch_assoc($result);

if(!$product){
echo "Product not found";
exit();
}

}else{
echo "Product not found";
exit();
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link rel="stylesheet" href="../css/style1.css">

<title><?php echo $product['name']; ?></title>

</head>

<body>

<div class="product-detail">

<div class="product-image">
<img src="../images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
</div>

<div class="product-info">

<h1><?php echo $product['name']; ?></h1>

<p class="price">
<?php echo number_format($product['price'],0,",","."); ?> đ
</p>

<p class="desc">
<?php echo $product['description']; ?>
</p>

<button class="buy-btn">Mua ngay</button>
<button class="cart-btn">Thêm vào giỏ hàng</button>

</div>

</div>

</body>
</html>