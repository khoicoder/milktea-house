<?php include("includes/header.php"); ?>

<!-- //http://localhost/milktea-house/ -->
<?php
require_once("config/config.php");
$select = "SELECT * FROM products";
$result = mysqli_query($conn, $select);

$category_id = $_GET['id'] ?? '';
$search = $_GET['search'] ?? '';
$min = $_GET['min'] ?? '';
$max = $_GET['max'] ?? '';

$search = mysqli_real_escape_string($conn, $search);

$sql = "SELECT * FROM products WHERE 1";

if ($category_id != '') {
    $sql .= " AND category_id = $category_id";
}

if ($search != '') {
    $sql .= " AND name LIKE '%$search%'";
}

if ($min != '' && $max != '') {
    $sql .= " AND price BETWEEN $min AND $max";
}

$result = mysqli_query($conn, $sql);
?>
<section class="products">

    <form method="GET">

        <input type="hidden" name="id" value="<?php echo $category_id; ?>">

        <input type="text" name="search" placeholder="Tìm sản phẩm">

        <label>Giá từ</label>
        <input type="number" name="min" value="0">

        <label>đến</label>
        <input type="number" name="max" value="100">

        <button type="submit">Lọc</button>

    </form>
    <h2>Đồ uống phổ biến</h2>

    <div class="grid">

        <?php while ($row = mysqli_fetch_assoc($result)) { ?>

            <div class="card">
                

                <div class="img-box">
                    <a href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $row['id'] ?>">
                        <img src="images/<?php echo $row['image']; ?>">
                    </a>
                </div>

                <h3><?php echo $row['name']; ?></h3>
                <p><?= number_format($row['price']) ?> VNĐ</p>
                <p><?php echo $row['description']; ?></p>


                <button onclick="addCart(<?php echo $row['id']; ?>)">thêm vào giỏ hàng</button>

            </div>

        <?php } ?>

    </div>

</section>


<?php include("includes/footer.php"); ?>