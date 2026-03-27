<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$orders = mysqli_query($conn, "
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON u.id = o.user_id
    ORDER BY o.id DESC
");

$statusLabels = [
    'pending'    => 'pending',
    'processing' => 'processing',
    'shipping'   => 'shipping',
    'completed'  => 'completed',
    'cancelled'  => 'cancelled',
];
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="dashboard">
    <h1>📦 Quản lý đơn hàng</h1>
    <a class="topbar-link" href="../dashboard.php">🏠 Trang chủ</a>

    <div class="box">
        <table>
            <tr>
                <th>Mã</th>
                <th>User</th>
                <th>Tổng</th>
                <th>Trạng thái</th>
                <th>Ngày</th>
                <th>Action</th>
            </tr>

            <?php while ($o = mysqli_fetch_assoc($orders)) {
                $status = trim((string)($o['status'] ?? ''));
                if ($status === '' || !isset($statusLabels[$status])) {
                    $status = 'pending';
                }
            ?>
                <tr>
                    <td>#<?= (int)$o['id'] ?></td>
                    <td><?= htmlspecialchars($o['username']) ?></td>
                    <td>$<?= htmlspecialchars($o['total']) ?></td>
                    <td>
                        <span class="badge <?= htmlspecialchars($status) ?>">
                            <?= htmlspecialchars($statusLabels[$status]) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($o['created_at']) ?></td>
                    <td>
                        <button onclick="deleteOrder(<?= (int)$o['id'] ?>)">❌</button>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

<script src="../js/admin.js"></script>