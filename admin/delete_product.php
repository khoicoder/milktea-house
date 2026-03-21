<?php
require_once("../config/config.php");
require_once("auth_admin.php");

if (!isset($_GET['id'])) {
    die("Thiếu ID");
}

$id = (int) $_GET['id'];

mysqli_query($conn, "DELETE FROM products WHERE id = $id");

header("Location: pages/manage_products.php");
exit;