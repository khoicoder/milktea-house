<?php include("../includes/header.php"); ?>
<?php

$category_id = $_GET['id'] ?? 0;
$search = $_GET['search'] ?? '';
$min = $_GET['min'] ?? 0;
$max = $_GET['max'] ?? 100;

$search = mysqli_real_escape_string($conn,$search);

$sql = "SELECT * FROM products 
        WHERE category_id = $category_id
        AND name LIKE '%$search%'
        AND price BETWEEN $min AND $max";

$result = mysqli_query($conn,$sql);



?>

<section class="products">

<h2>Sản phẩm</h2>

<form method="GET">

<input type="hidden" name="id" value="<?php echo $category_id; ?>">

<input type="text" name="search" placeholder="Tìm sản phẩm">

<label>Giá từ</label>
<input type="number" name="min" value="0">

<label>đến</label>
<input type="number" name="max" value="100">

<button type="submit">Lọc</button>

</form>

<div class="grid">

<?php while($row = mysqli_fetch_assoc($result)){ ?>

<div class="card">

<div class="img-box">
<img src="../images/<?php echo $row['image']; ?>">
</div>

<h3><?php echo $row['name']; ?></h3>

<p>$<?php echo $row['price']; ?></p>

<button onclick="addCart(<?php echo $row['id']; ?>)">thêm vào giỏ hàng</button>

</div>

<?php } ?>

</div>

</section>

<?php include("../includes/footer.php"); ?>

















`       `       ``