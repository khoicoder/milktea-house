<?php
require_once("../../config/config.php");

$type = $_GET['period'] ?? 'month';
$from = $_GET['from_date'] ?? date("Y-m-01");
$to   = $_GET['to_date'] ?? date("Y-m-d");

if ($type === 'day') {
    $labelSql = "DATE(created_at)";
    $groupSql = "DATE(created_at)";
} else {
    $labelSql = "DATE_FORMAT(created_at, '%Y-%m')";
    $groupSql = "DATE_FORMAT(created_at, '%Y-%m')";
}

$sql = "SELECT $labelSql AS label,
        COALESCE(SUM(total), 0) AS revenue
    FROM orders
    WHERE DATE(created_at) BETWEEN '$from' AND '$to'
    GROUP BY $groupSql
    ORDER BY label ASC
";

$result = mysqli_query($conn, $sql);

$data = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($data);