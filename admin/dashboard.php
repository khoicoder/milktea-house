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
    SELECT id, user_id, total, status, created_at
    FROM orders
    ORDER BY created_at DESC
    LIMIT 5
");
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - MilkTea House</title>
    <link rel="stylesheet" href="../css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../js/admin_chart.js" defer ></script>
</head>
<body>

<div class="layout">

    <aside class="sidebar">
        <h2>🧋 MilkTea Admin</h2>
        <a href="dashboard.php">📊 Dashboard</a>
        <a href="pages/manage_products.php">📦 Sản phẩm</a>
        <a href="pages/orders.php">🧾 Đơn hàng</a>
        <a href="pages/users.php">👤 Người dùng</a>
        <a href="pages/notifications.php">🔔 Thông báo</a>
    </aside>

    <main class="main">

        <div class="topbar">
            <div>
                <h1>Dashboard</h1>
                <p class="subtext">Tổng quan hoạt động hệ thống</p>
            </div>
            <a class="topbar-link" href="../index.php">🏠 Trang chủ</a>
        </div>

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

        <section class="box chart-box">
            <div class="box-head">
                <div>
                    <h2>📈 Doanh thu</h2>
                    <p class="subtext">Lọc theo khoảng thời gian, đổi kiểu biểu đồ linh hoạt</p>
                </div>
            </div>

            <form id="filterForm" class="chart-toolbar">
                <div class="field">
                    <label>Từ ngày</label>
                    <input type="date" name="from" value="<?= htmlspecialchars($from) ?>">
                </div>

                <div class="field">
                    <label>Đến ngày</label>
                    <input type="date" name="to" value="<?= htmlspecialchars($to) ?>">
                </div>

                <div class="field">
                    <label>Nhóm theo</label>
                    <select name="period" id="period">
                        <option value="day" <?= $period === 'day' ? 'selected' : '' ?>>Theo ngày</option>
                        <option value="month" <?= $period === 'month' ? 'selected' : '' ?>>Theo tháng</option>
                    </select>
                </div>

                <div class="field">
                    <label>Kiểu chart</label>
                    <select name="chartType" id="chartType">
                        <option value="bar" <?= $chartType === 'bar' ? 'selected' : '' ?>>Bar</option>
                        <option value="line" <?= $chartType === 'line' ? 'selected' : '' ?>>Line</option>
                    </select>
                </div>

                <button type="submit" class="btn-primary">Lọc</button>
            </form>

            <div class="chart-wrap">
                <canvas id="revenueChart" height="110"></canvas>
            </div>
        </section>

        <section class="box action">
            <div>
                <h2>🔔 Gửi thông báo</h2>
                <p class="subtext">Gửi thông báo mới cho khách hàng nhanh chóng.</p>
            </div>
            <a class="btn-primary" href="pages/notifications.php">Gửi ngay →</a>
        </section>

        <section class="grid-2">
            <div class="box">
                <h2>🔥 Top sản phẩm</h2>
                <table>
                    <tr><th>Tên</th><th>Bán</th></tr>
                    <?php if ($topProducts) while ($p = mysqli_fetch_assoc($topProducts)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($p['name']) ?></td>
                            <td><?= number_format($p['total']) ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>

            <div class="box">
                <h2>👤 User mới</h2>
                <table>
                    <tr><th>Username</th><th>Email</th></tr>
                    <?php if ($newUsers) while ($u = mysqli_fetch_assoc($newUsers)) { ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td><?= htmlspecialchars($u['email']) ?></td>
                        </tr>
                    <?php } ?>
                </table>
            </div>
        </section>

        <section class="box">
            <h2>🧾 Đơn hàng gần đây</h2>
            <table>
                <tr>
                    <th>Mã đơn</th>
                    <th>User ID</th>
                    <th>Tổng tiền</th>
                    <th>Trạng thái</th>
                    <th>Ngày tạo</th>
                </tr>
                <?php if ($recentOrders) while ($o = mysqli_fetch_assoc($recentOrders)) { ?>
                    <tr>
                        <td>#<?= (int)$o['id'] ?></td>
                        <td><?= (int)$o['user_id'] ?></td>
                        <td><?= number_format((float)$o['total'], 0, ',', '.') ?>đ</td>
                        <td><?= htmlspecialchars($o['status']) ?></td>
                        <td><?= htmlspecialchars($o['created_at']) ?></td>
                    </tr>
                <?php } ?>
            </table>
        </section>

    </main>
</div>

</body>
</html>