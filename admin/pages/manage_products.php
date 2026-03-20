<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$where = "WHERE 1";

// search
if(!empty($_GET['keyword'])){
    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
    $where .= " AND p.name LIKE '%$keyword%'";
}

// filter category
if(!empty($_GET['category'])){
    $cat = (int)$_GET['category'];
    $where .= " AND p.category_id = $cat";
}

$products = mysqli_query($conn,"
SELECT p.*, c.name as category 
FROM products p 
LEFT JOIN categories c ON c.id = p.category_id
$where
ORDER BY p.id DESC
");
?>
<link rel="stylesheet" href="<?= BASE_URL ?>admin/css/admin_manage_products.css">

<div class="dashboard">
<h1>🧋 Quản lý sản phẩm</h1>

<a href="add_product.php">➕ Thêm sản phẩm</a>

<div class="box">
<form method="GET" class="filter-box">
    <input type="text" name="keyword" placeholder="Tìm sản phẩm...">

    <select name="category">
        <option value="">-- Danh mục --</option>
        <?php 
        $cats = mysqli_query($conn,"SELECT * FROM categories");
        while($c = mysqli_fetch_assoc($cats)){ ?>
            <option value="<?= $c['id'] ?>">
                <?= $c['name'] ?>
            </option>
        <?php } ?>
    </select>

    <button type="submit">Lọc</button>
</form>

<table>
<tr>
<th>ID</th>
<th>Image</th>
<th>Name</th>
<th>Price</th>
<th>Stock</th>
<th>Category</th>
<th>Action</th>
</tr>

<?php while($p = mysqli_fetch_assoc($products)){ ?>
<tr>
<td><?= $p['id'] ?></td>

<td>
    <img src="../../uploads/<?= $p['image'] ?>" class="product-img">
</td>

<td><?= $p['name'] ?></td>

<td><?= number_format($p['price'], 0, ',', '.') ?>đ</td>

<td>
    <?php if($p['stock'] > 0){ ?>
        <span class="badge in-stock">Còn hàng</span>
    <?php } else { ?>
        <span class="badge out-stock">Hết hàng</span>
    <?php } ?>
</td>

<td><?= $p['category'] ?></td>

<td class="actions">
    <a class="edit" href="edit_product.php?id=<?= $p['id'] ?>">✏️</a>
    <a class="delete" href="../delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Xóa?')">🗑</a>
</td>
</tr>
<?php } ?>
</table>
</div>
</div>