<?php
require_once("../../config/config.php");
require_once("../auth_admin.php");

$users = mysqli_query($conn,"SELECT * FROM users ORDER BY id DESC");
?>

<link rel="stylesheet" href="../css/admin.css">

<div class="dashboard">
<h1>👤 Quản lý User</h1>

<div class="box">
<table>
<tr>
<th>ID</th>
<th>Username</th>
<th>Email</th>
<th>Action</th>
</tr>

<?php while($u = mysqli_fetch_assoc($users)){ ?>
<tr>
<td><?= $u['id'] ?></td>
<td><?= $u['username'] ?></td>
<td><?= $u['email'] ?></td>
<td>
<a href="../delete_user.php?id=<?= $u['id'] ?>" onclick="return confirm('Xóa user?')">❌</a>
</td>
</tr>
<?php } ?>

</table>
</div>
</div>