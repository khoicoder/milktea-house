<?php
require_once("../../config/config.php");

$result = mysqli_query($conn,"SELECT * FROM users");

$data = [];
while($row = mysqli_fetch_assoc($result)){
    $data[] = $row;
}

echo json_encode($data);