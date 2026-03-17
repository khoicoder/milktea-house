<?php
require_once("../config/config.php");
header('Content-Type: application/json');

// Nhận chuỗi JSON chứa các ID từ Javascript gửi lên
$ids_json = isset($_POST['ids']) ? $_POST['ids'] : '[]';

// Chuyển chuỗi JSON ngược lại thành mảng PHP
$ids = json_decode($ids_json, true);

// Kiểm tra xem dữ liệu có phải là mảng và không rỗng
if (is_array($ids) && !empty($ids)) {
    foreach ($ids as $id) {
        // Lặp qua từng ID và xóa nó khỏi Session nếu tồn tại
        if (isset($_SESSION['cart'][$id])) {
            unset($_SESSION['cart'][$id]);
        }
    }
}

// Đếm lại tổng số lượng sản phẩm còn trong giỏ
$count = empty($_SESSION['cart']) ? 0 : array_sum($_SESSION['cart']);

// Trả về kết quả
echo json_encode([
    "status" => "success",
    "message" => "Đã xóa các sản phẩm được chọn",
    "cart_count" => $count
]);

