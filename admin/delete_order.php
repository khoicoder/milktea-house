<?php
require_once("../config/config.php");
require_once("auth_admin.php");

if (!isset($_POST['id'])) {
    echo json_encode(["status"=>"error"]);
    exit;
}

$id = (int) $_POST['id'];

mysqli_query($conn,"DELETE FROM orders WHERE id=$id");

echo json_encode(["status"=>"success"]);