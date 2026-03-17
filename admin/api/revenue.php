<?php
require_once("../../config/config.php");

$sql = "SELECT DATE(created_at) as day, SUM(total) as revenue 
        FROM orders 
        GROUP BY DATE(created_at) 
        ORDER BY day ASC";

$result = mysqli_query($conn,$sql);

$data = [];

while($row = mysqli_fetch_assoc($result)){
$data[] = $row;
}

echo json_encode($data);