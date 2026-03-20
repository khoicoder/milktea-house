<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

// Lấy id an toàn
$id = (int)$_GET['id'];

// Lấy product
$product = mysqli_fetch_assoc(mysqli_query($conn,"SELECT * FROM products WHERE id=$id"));

// Lấy categories
$categories = mysqli_query($conn, "SELECT * FROM categories");

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category = $_POST['category_id'];

    mysqli_query($conn,"
    UPDATE products 
    SET name='$name', price='$price', stock='$stock', category_id='$category'
    WHERE id=$id
    ");

    header("Location: manage_products.php");
    exit;
}
?>

<link rel="stylesheet" href="../css/admin_products.css">

<div class="dashboard">

    <div class="topbar">
        <h1>✏️ Sửa sản phẩm</h1>
        <a href="manage_products.php">← Quay lại</a>
    </div>

    <div class="form-box">
        <form method="POST">

            <div class="form-group">
                <label>Tên sản phẩm</label>
                <input name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
            </div>

            <div class="form-group">
                <label>Giá</label>
                <input type="number" name="price" value="<?= $product['price'] ?>" required>
            </div>

            <div class="form-group">
                <label>Số lượng</label>
                <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
            </div>

            <div class="form-group">
                <label>Danh mục</label>
                <select name="category_id" required>
                    <?php while($c = mysqli_fetch_assoc($categories)) { ?>
                        <option value="<?= $c['id'] ?>"
                            <?= $c['id'] == $product['category_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <button class="btn-save">💾 Cập nhật</button>

        </form>
    </div>

</div>