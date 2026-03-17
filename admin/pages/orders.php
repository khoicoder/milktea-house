<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$orders = mysqli_query($conn,"
SELECT o.*, u.username 
FROM orders o 
JOIN users u ON u.id = o.user_id
ORDER BY o.id DESC
");
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="dashboard">
<h1>📦 Quản lý đơn hàng</h1>

<div class="box">
<table>
<tr>
<th>ID</th>
<th>User</th>
<th>Total</th>
<th>Status</th>
<th>Date</th>
<th>Action</th>
</tr>

<?php while($o = mysqli_fetch_assoc($orders)){ ?>
<tr>
<td>#<?= $o['id'] ?></td>
<td><?= $o['username'] ?></td>
<td>$<?= $o['total'] ?></td>
<td><span class="badge <?= $o['status'] ?>"><?= $o['status'] ?></span></td>
<td><?= $o['created_at'] ?></td>
<td>
<button onclick="deleteOrder(<?= $o['id'] ?>)">❌</button>
</td>
</tr>
<?php } ?>

</table>
</div>
</div>
<script src="../js/admin.js"></script>