<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$products = mysqli_query($conn,"
SELECT p.*, c.name as category 
FROM products p 
LEFT JOIN categories c ON c.id = p.category_id
ORDER BY p.id DESC
");
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="dashboard">
<h1>🧋 Quản lý sản phẩm</h1>

<a href="add_product.php">➕ Thêm sản phẩm</a>

<div class="box">
<table>
<tr>
<th>ID</th>
<th>Name</th>
<th>Price</th>
<th>Stock</th>
<th>Category</th>
<th>Action</th>
</tr>

<?php while($p = mysqli_fetch_assoc($products)){ ?>
<tr>
<td><?= $p['id'] ?></td>
<td><?= $p['name'] ?></td>
<td>$<?= $p['price'] ?></td>
<td><?= $p['stock'] ?></td>
<td><?= $p['category'] ?></td>
<td>
<a href="edit_product.php?id=<?= $p['id'] ?>">✏️</a>
<a href="../delete_product.php?id=<?= $p['id'] ?>" onclick="return confirm('Xóa?')">❌</a>
</td>
</tr>
<?php } ?>

</table>
</div>
</div>