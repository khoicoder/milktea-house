<?php require_once("../config/config.php"); 

// Lấy danh mục
$cate_sql = "SELECT id, name FROM categories";
$cate_result = mysqli_query($conn, $cate_sql);

// Filter input
$category_id = intval($_GET['id'] ?? 0);
$search = $_GET['search'] ?? '';
$min = isset($_GET['min']) && $_GET['min'] !== '' ? intval($_GET['min']) : 0;
$max = isset($_GET['max']) && $_GET['max'] !== '' ? intval($_GET['max']) : 1000000;

if ($min > $max) {
    [$min, $max] = [$max, $min];
}

$search_esc = mysqli_real_escape_string($conn, $search);

// SQL JOIN để lấy tên danh mục
$sql = "SELECT p.id, p.name, p.price, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.price BETWEEN {$min} AND {$max}";

if ($category_id > 0) {
    $sql .= " AND p.category_id = {$category_id}";
}

if ($search_esc !== '') {
    $sql .= " AND p.name LIKE '%{$search_esc}%'";
}

$sql .= " ORDER BY p.id DESC LIMIT 10";

$result = mysqli_query($conn, $sql);


?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>Admin Dashboard</title>
<link rel="stylesheet" href="../css/admin.css">
</head>

<body>

<div class="admin-container">

  <!-- Sidebar -->
  <div class="sidebar">
    <h2>Admin</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="add_product.php">Thêm sản phẩm</a>
    <a href="manage_products.php">Quản lý sản phẩm</a>
    <a href="orders.php">Đơn hàng</a>
    <a href="users.php">Người dùng</a>
    <a href="../index.php">← Về trang chính</a>
  </div>

  <!-- Content -->
  <div class="main">

    <div class="topbar">
      <h1>Dashboard</h1>
      <div>Xin chào, Admin</div>
    </div>

    <!-- Stats -->
    <div class="stats">
      <div class="card"><h3>Sản phẩm</h3><p>50</p></div>
      <div class="card"><h3>Đơn hàng</h3><p>120</p></div>
      <div class="card"><h3>Người dùng</h3><p>35</p></div>
      <div class="card"><h3>Doanh thu</h3><p>$1200</p></div>
    </div>

    <!-- Filter -->
    <form method="GET" class="filter-form" style="display:flex;gap:10px;flex-wrap:wrap;margin:20px 0;">
    <!-- Pagination -->


      <!-- Category -->
      <select name="id" onchange="this.form.submit()" style="padding:8px;border-radius:8px;">
        <option value="0">📂 Tất cả danh mục</option>
        <?php while($cat = mysqli_fetch_assoc($cate_result)){ ?>
          <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>>
            <?= $cat['name'] ?>
          </option>
        <?php } ?>
      </select>

      <!-- Search -->
      <input type="text" name="search" placeholder="🔍 Tìm sản phẩm..."
        value="<?= htmlspecialchars($search) ?>"
        style="flex:1;min-width:200px;padding:8px;border-radius:8px;">

      <!-- Price -->
      <input type="number" name="min" value="<?= $min ?>" placeholder="Giá từ" style="width:120px;">
      <input type="number" name="max" value="<?= $max ?>" placeholder="Đến" style="width:120px;">

      <button type="submit">Lọc</button>
      <a href="dashboard.php" style="padding:8px 12px;background:#eee;border-radius:8px;">Reset</a>

    </form>

    <!-- Chart -->
    <canvas id="revenueChart" height="80"></canvas>

    <!-- Table -->
    <div class="table-box">
      <h2> Danh sách sản phẩm</h2>

      <table>
        <tr>
          <th>ID</th>
          <th>Tên</th>
          <th>Danh mục</th>
          <th>Giá</th>
          <th>Hành động</th>
        </tr>

        <?php if(mysqli_num_rows($result) > 0){ ?>
          <?php while($row = mysqli_fetch_assoc($result)){ ?>
          <tr>
            <td><?= $row['id'] ?></td>
            <td><?= $row['name'] ?></td>
            <td><?= $row['category_name'] ?? 'Không có' ?></td>
            <td><?= number_format($row['price']) ?>đ</td>
            <td>
              <a href="edit_product.php?id=<?= $row['id'] ?>">✏️ Sửa</a>
            </td>
          </tr>
          <?php } ?>
        <?php } else { ?>
          <tr>
            <td colspan="5" style="text-align:center;">❌ Không có sản phẩm</td>
          </tr>
        <?php } ?>

      </table>
    </div>

  </div>
</div>

</body>
</html>

<script>
const ctx = document.getElementById('revenueChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['T1','T2','T3','T4','T5','T6'],
        datasets: [{
            label: 'Doanh thu',
            data: [1200,1500,1800,1700,2000,2200],
            fill: true,
            tension: 0.4
        }]
    },
    options: { responsive: true }
});
</script>