<?php
require_once("../config/config.php");
require_once("auth_admin.php");

// FILTER DATE
$from = $_GET['from'] ?? date("Y-m-01");
$to   = $_GET['to'] ?? date("Y-m-d");

// STATS
$totalUsers = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM users"))[0];
$totalProducts = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM products"))[0];
$totalOrders = mysqli_fetch_row(mysqli_query($conn,"SELECT COUNT(*) FROM orders"))[0];

$revenue = mysqli_fetch_row(mysqli_query($conn,"
SELECT SUM(total) FROM orders 
WHERE DATE(created_at) BETWEEN '$from' AND '$to'
"))[0] ?? 0;

// TOP PRODUCTS
$topProducts = mysqli_query($conn,"
SELECT p.name, COUNT(oi.product_id) as total
FROM order_items oi
JOIN products p ON p.id = oi.product_id
GROUP BY oi.product_id
ORDER BY total DESC
LIMIT 5
");

// NEW USERS
$newUsers = mysqli_query($conn,"
SELECT * FROM users ORDER BY created_at DESC LIMIT 5
");

// RECENT ORDERS
$orders = mysqli_query($conn,"
SELECT * FROM orders ORDER BY created_at DESC LIMIT 5
");
?>

<link rel="stylesheet" href="../css/admin.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<div class="dashboard">

<h1>📊 Admin Dashboard</h1>

<!-- FILTER -->
<form method="GET" class="filter-box">
  <label>Từ:</label>
  <input type="date" name="from" value="<?= $from ?>">
  
  <label>Đến:</label>
  <input type="date" name="to" value="<?= $to ?>">
  
  <button>Lọc</button>
</form>

<!-- STATS -->
<div class="stats">

<div class="card">
<h3>Users</h3>
<p><?= $totalUsers ?></p>
</div>

<div class="card">
<h3>Products</h3>
<p><?= $totalProducts ?></p>
</div>

<div class="card">
<h3>Orders</h3>
<p><?= $totalOrders ?></p>
</div>

<div class="card">
<h3>Revenue</h3>
<p>$<?= $revenue ?></p>
</div>

</div>

<!-- CHART -->
<div class="chart-box">
<canvas id="chart"></canvas>
</div>

<!-- FLEX BOX -->
<div class="grid-2">

<!-- TOP PRODUCTS -->
<div class="box">
<h2>🔥 Top sản phẩm</h2>

<table>
<tr><th>Tên</th><th>Đã bán</th></tr>

<?php while($p = mysqli_fetch_assoc($topProducts)){ ?>
<tr>
<td><?= $p['name'] ?></td>
<td><?= $p['total'] ?></td>
</tr>
<?php } ?>

</table>
</div>

<!-- USERS -->
<div class="box">
<h2>👤 User mới</h2>

<table>
<tr><th>Username</th><th>Email</th></tr>

<?php while($u = mysqli_fetch_assoc($newUsers)){ ?>
<tr>
<td><?= $u['username'] ?></td>
<td><?= $u['email'] ?></td>
</tr>
<?php } ?>

</table>

<a href="pages/users.php">Xem thêm →</a>
</div>

</div>

<!-- ORDERS -->
<div class="box">
<h2>📦 Đơn hàng gần đây</h2>

<table>
<tr>
<th>ID</th>
<th>Total</th>
<th>Status</th>
<th>Date</th>
</tr>

<?php while($o = mysqli_fetch_assoc($orders)){ ?>
<tr>
<td>#<?= $o['id'] ?></td>
<td>$<?= $o['total'] ?></td>
<td><?= $o['status'] ?></td>
<td><?= $o['created_at'] ?></td>
</tr>
<?php } ?>

</table>

<a href="pages/orders.php">Xem tất cả →</a>

</div>

</div>

<script src="js/admin.js"></script>