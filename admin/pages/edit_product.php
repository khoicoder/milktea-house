<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$id = $_GET['id'];
$product = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE id=$id"));

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    mysqli_query($conn,"
    UPDATE products 
    SET name='$name', price='$price', stock='$stock'
    WHERE id=$id
    ");

    header("Location: manage_products.php");
}
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="dashboard">
<h1>✏️ Sửa sản phẩm</h1>

<div class="box">
<form method="POST">
<input name="name" value="<?= $product['name'] ?>"><br><br>
<input name="price" value="<?= $product['price'] ?>"><br><br>
<input name="stock" value="<?= $product['stock'] ?>"><br><br>

<button>Cập nhật</button>
</form>
</div>
</div>