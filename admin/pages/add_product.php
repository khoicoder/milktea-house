<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    mysqli_query($conn,"
    INSERT INTO products(name,price,stock)
    VALUES('$name','$price','$stock')
    ");

    header("Location: manage_products.php");
}
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="dashboard">
<a class="topbar-link" href="../dashboard.php">🏠 Trang chủ</a>
<h1>➕ Thêm sản phẩm</h1>

<div class="box">
<form method="POST">
<input name="name" placeholder="Tên sản phẩm"><br><br>
<input name="price" placeholder="Giá"><br><br>
<input name="stock" placeholder="Stock"><br><br>

<button>Thêm</button>
</form>
</div>
</div>