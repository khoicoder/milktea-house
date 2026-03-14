<?php
require_once("../config/config.php");
session_unset();
session_destroy();
header("Location: " . BASE_URL . "index.php");
exit;