<?php
require_once("../config/config.php");
require_once("auth_admin.php");

$id = $_POST['id'];

mysqli_query($conn,"DELETE FROM products WHERE id=$id");

echo json_encode([
"status"=>"success"
]);