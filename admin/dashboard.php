<?php
require_once("../config/config.php");
require_once("auth_admin.php");
// Hàm helper để query an toàn
function get_stat($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) return "Lỗi SQL";
    $row = mysqli_fetch_row($result);
    return $row ? $row[0] : 0;
}

$from = $_GET['from'] ?? date("Y-m-01");
$to   = $_GET['to'] ?? date("Y-m-d");

$totalUsers = get_stat($conn, "SELECT COUNT(*) FROM users");
$totalProducts = get_stat($conn, "SELECT COUNT(*) FROM products");
$totalOrders = get_stat($conn, "SELECT COUNT(*) FROM orders");
$revenue = get_stat($conn, "SELECT SUM(total) FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to'");
$revenue_display = is_numeric($revenue) ? number_format($revenue, 0, ',', '.') . "đ" : "0đ";

$topProducts = mysqli_query($conn, "SELECT p.name, COUNT(oi.product_id) as total FROM order_items oi JOIN products p ON p.id = oi.product_id GROUP BY oi.product_id ORDER BY total DESC LIMIT 5");
$newUsers = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$orders = mysqli_query($conn, "SELECT * FROM orders ORDER BY created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MilkTea House</title>
    <link rel="stylesheet" href="../css/admin.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f4f7f6; margin: 0; padding: 0; }
        .dashboard { max-width: 1200px; margin: 0 auto; padding: 20px; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .card { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); text-align: center; }
        .card h3 { margin: 0; color: #666; font-size: 16px; }
        .card p { font-size: 24px; font-weight: bold; color: #ff5a5f; margin: 10px 0 0; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .box { background: #fff; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        table th, table td { text-align: left; padding: 12px; border-bottom: 1px solid #eee; font-size: 14px; }
        .btn-noti { display: inline-block; padding: 10px 20px; background: #ff5a5f; color: white; border-radius: 5px; text-decoration: none; font-weight: 500; transition: background 0.3s; }
        .btn-noti:hover { background: #e04a4f; }
    </style>
</head>
<body>

<div class="dashboard">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <h1>📊 Admin Dashboard</h1>
        <a href="../index.php" style="text-decoration: none; color: #ff5a5f; font-weight: 500;">← Quay lại Trang chủ</a>
    </div>

    <!-- STATS -->
    <div class="stats">
        <div class="card"><h3>Users</h3><p><?= $totalUsers ?></p></div>
        <div class="card"><h3>Sản phẩm</h3><p><?= $totalProducts ?></p></div>
        <div class="card"><h3>Đơn hàng</h3><p><?= $totalOrders ?></p></div>
        <div class="card"><h3>Doanh thu</h3><p><?= $revenue_display ?></p></div>
    </div>

    <div class="box" style="border-left: 5px solid #ff5a5f;">
        <h2>🔔 Quản lý Thông báo</h2>
        <p style="color: #666;">Gửi thông báo mới cho khách hàng ngay bây giờ.</p>
        <a href="pages/notifications.php" class="btn-noti">Gửi thông báo →</a>
    </div>

    <div class="grid-2">
        <div class="box">
            <h2>🔥 Sản phẩm bán chạy</h2>
            <table>
                <tr><th>Tên</th><th>Bán</th></tr>
                <?php if ($topProducts) while($p = mysqli_fetch_assoc($topProducts)){ ?>
                <tr><td><?= htmlspecialchars($p['name']) ?></td><td><?= $p['total'] ?></td></tr>
                <?php } ?>
            </table>
        </div>
        <div class="box">
            <h2>👤 Người dùng</h2>
            <table>
                <tr><th>Username</th><th>Email</th></tr>
                <?php if ($newUsers) while($u = mysqli_fetch_assoc($newUsers)){ ?>
                <tr><td><?= htmlspecialchars($u['username']) ?></td><td><?= htmlspecialchars($u['email']) ?></td></tr>
                <?php } ?>
            </table>
        </div>
    </div>
</div>

</body>
</html>