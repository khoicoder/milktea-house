<?php
include_once(__DIR__ . "/../config/config.php");
if (!isset($_SESSION['admin'])) {
    header("Location: ../index.php");
    exit;
}
?>