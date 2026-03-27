<?php
require_once("../config/config.php");
require_once("auth_admin.php");

function get_stat($conn, $sql) {
    $result = mysqli_query($conn, $sql);
    if (!$result) return 0;
    $row = mysqli_fetch_row($result);
    return $row ? (float)$row[0] : 0;
}

$from    = $_GET['from'] ?? date("Y-m-01");
$to      = $_GET['to'] ?? date("Y-m-d");
$period  = $_GET['period'] ?? 'month';
$chartType = $_GET['chartType'] ?? 'bar';

$totalUsers    = get_stat($conn, "SELECT COUNT(*) FROM users");
$totalProducts = get_stat($conn, "SELECT COUNT(*) FROM products");
$totalOrders   = get_stat($conn, "SELECT COUNT(*) FROM orders");
$revenue       = get_stat($conn, "SELECT SUM(total) FROM orders WHERE DATE(created_at) BETWEEN '$from' AND '$to'");
$revenue_display = number_format((float)$revenue, 0, ',', '.') . "đ";

$currentMonthStart = date("Y-m-01");
$nextMonthStart    = date("Y-m-01", strtotime("+1 month"));
$prevMonthStart    = date("Y-m-01", strtotime("-1 month"));

$currentMonthRevenue = get_stat($conn, "
    SELECT SUM(total) 
    FROM orders
    WHERE DATE(created_at) >= '$currentMonthStart' 
      AND DATE(created_at) < '$nextMonthStart'
");

$lastMonthRevenue = get_stat($conn, "
    SELECT SUM(total) 
    FROM orders
    WHERE DATE(created_at) >= '$prevMonthStart' 
      AND DATE(created_at) < '$currentMonthStart'
");

$growth = ($lastMonthRevenue > 0)
    ? (($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100
    : 0;

$topProducts = mysqli_query($conn, "
    SELECT p.name, COUNT(oi.product_id) as total
    FROM order_items oi
    JOIN products p ON p.id = oi.product_id
    GROUP BY oi.product_id
    ORDER BY total DESC
    LIMIT 5
");

$newUsers = mysqli_query($conn, "
    SELECT username, email, created_at
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");

$recentOrders = mysqli_query($conn, "
    SELECT o.id, o.user_id, u.username, o.total, o.status, o.created_at
    FROM orders o
    JOIN users u ON u.id = o.user_id
    ORDER BY o.created_at DESC
    LIMIT 5
");
$statusLabels = [
    'pending'    => 'pending',
    'processing' => 'processing',
    'shipping'   => 'shipping',
    'completed'  => 'completed',
    'cancelled'  => 'cancelled',
];
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MilkTea House</title>
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="js/admin_chart.js" defer >"lỗi"</script>
</head>
<body>
<div class="layout">

    <!-- ===== SIDEBAR ===== -->
    <aside class="sidebar">
        <h2>🧋 MilkTea Admin</h2>

        <a class="active" href="dashboard.php">📊 Dashboard</a>
        <a href="pages/manage_products.php">📦 Sản phẩm</a>
        <a href="pages/orders.php">🧾 Đơn hàng</a>
        <a href="pages/users.php">👤 Người dùng</a>
        <a href="pages/notifications.php">🔔 Thông báo</a>
    </aside>

    <!-- ===== MAIN ===== -->
    <main class="main">

        <!-- TOPBAR -->
        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p class="subtext">Tổng quan hoạt động hệ thống</p>
            </div>
            <a class="topbar-link" href="../index.php">🏠 Trang chủ</a>
        </div>

        <!-- STATS -->
        <section class="stats">
            <div class="card">
                <h3>Users</h3>
                <p><?= number_format($totalUsers) ?></p>
            </div>

            <div class="card">
                <h3>Sản phẩm</h3>
                <p><?= number_format($totalProducts) ?></p>
            </div>

            <div class="card">
                <h3>Đơn hàng</h3>
                <p><?= number_format($totalOrders) ?></p>
            </div>

            <div class="card highlight">
                <h3>Doanh thu</h3>
                <p><?= $revenue_display ?></p>
            </div>
        </section>

        <!-- CHART -->
        <section class="chart-box" >
            <div class="box-head">
                <h2>📈 Doanh thu</h2>
                <p class="subtext">Lọc theo khoảng thời gian</p>
            </div>

            <form class="chart-toolbar" id="filterForm">
                <div class="field" >
                    <label>Từ ngày</label>
                    <input type="date" name="from_date">
                </div>

                <div class="field">
                    <label>Đến ngày</label>
                    <input type="date" name = "to_date">
                </div>

                <div class="field">
                    <label>Nhóm theo</label>
                    <select name="period" id="periodSelect"> >
                        <option value="day" selected>Theo ngày</option>
                        <option value="month">Theo tháng</option>
                    </select>
                </div>

                <div class="field">
                    <label>Kiểu chart</label>
                    <select name="chart_type" id="chartType">
                        <option value="bar" >Bar</option>
                        <option value="line" selected>Line</option>
                    </select>
                </div>

                <button class="btn-primary" type="submit">Lọc</button>
            </form>

            <div class="chart-wrap">
                <canvas id="revenueChart"></canvas>
            </div>
        </section>

        <!-- ACTION -->
        <section class="box action">
            <div>
                <h2>🔔 Gửi thông báo</h2>
                <p class="subtext">Gửi thông báo nhanh cho khách hàng</p>
            </div>
            <a class="btn-primary" href="pages/notifications.php" style="color: white;">Gửi ngay →</a>
        </section>

        <!-- GRID -->
        <section class="grid-2">

            <div class="box">
                <h2>🔥 Top sản phẩm</h2>
                <table>
                    <tr><th>Tên</th><th>Bán</th></tr>
                    <?php while ($p = mysqli_fetch_assoc($topProducts)) { ?>
                        <tr>
                            <td><?= $p['name'] ?></td>
                            <td><?= $p['total'] ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="box">
                <h2>👤 User mới</h2>
                <table>
                    <tr><th>Username</th><th>Email</th></tr>
                    <?php while ($u = mysqli_fetch_assoc($newUsers)) { ?>
                        <tr>
                            <td><?= $u['username'] ?></td>
                            <td><?= $u['email'] ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

        </section>

        <!-- ORDERS -->
        <section class="box">
            <h2>🧾 Đơn hàng gần đây</h2>
            <table>
                <tr>
                    <th>Mã</th>
                    <th>User</th>
                    <th>Tổng</th>
                    <th>Trạng thái</th>
                    <th>Ngày</th>
                </tr>

                <?php while ($o = mysqli_fetch_assoc($recentOrders)) {
                $status = trim((string)($o['status'] ?? ''));
                if ($status === '' || !isset($statusLabels[$status])) {
                    $status = 'pending';
                }
            ?>
                <tr>
                    <td>#<?= (int)$o['id'] ?></td>
                    <td><?= htmlspecialchars($o['username']) ?></td>
                    <td><?= htmlspecialchars($o['total']) ?> VNĐ</td>
                    <td>
                        <span class="badge <?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars($statusLabels[$status]) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($o['created_at']) ?></td>
                    </tr>
                <?php 
                } ?>
            </table>
        </section>

    </main>
</div>

</body>
</html>