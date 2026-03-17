<?php require_once("../config/config.php"); 
$sql = "SELECT id, username, email, created_at FROM users ORDER BY id DESC";
$result = mysqli_query($conn, $sql);
foreach ($result as $row) {
    <htm
    <div class="card">
        <h3><?= $row['username'] ?></h3>
        <p><?= $row['email'] ?></p>
        <p>Created At: <?= $row['created_at'] ?></p>
    </div>
}
?>