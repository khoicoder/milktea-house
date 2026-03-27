<?php
include("../config/config.php");
$page_css = "auth.css";
if(isset($page_css)){
    echo '<link rel="stylesheet" href="'.BASE_URL.'css/' . $page_css . '">';
}
$message = "";

// 1. Lấy token từ URL
$token = $_GET['token'] ?? '';

// 2. Kiểm tra token hợp lệ

$current = date("Y-m-d H:i:s");

$sql = "SELECT * FROM users 
        WHERE reset_token = '$token' 
        AND reset_token_expire > '$current'";

$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Token không hợp lệ hoặc đã hết hạn";
    exit;
}

// 3. Xử lý khi submit form
if (isset($_POST['reset'])) {
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];

    // validate
    if (empty($password) || empty($confirm)) {
        $message = "Vui lòng nhập đầy đủ";
    } elseif ($password != $confirm) {
        $message = "Mật khẩu không khớp";
    } elseif (strlen($password) < 6) {
        $message = "Mật khẩu phải >= 6 ký tự";
    } else {
        // 4. Hash mật khẩu
        $hash = password_hash($password, PASSWORD_DEFAULT);

        // 5. Update DB + xóa token
        $sql = "UPDATE users 
                SET password = '$hash',
                    reset_token = NULL,
                    reset_token_expire = NULL
                WHERE reset_token = '$token'";

        mysqli_query($conn, $sql);

        $message = "Đổi mật khẩu thành công";
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        header("Location: ../index.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Reset mật khẩu</title>
</head>

<body>

    <h2>Đặt lại mật khẩu</h2>

    <p><?php echo $message; ?></p>

    <form method="post">
        <label>Mật khẩu mới</label>
        <input type="password" name="password" id="password" placeholder="Mật khẩu mới">
        <span class="auth-error" id="passwordError">
            <?php echo $passwordError ?? ''; ?>
        </span><br><br>

        <label>Xác nhận lại mật khẩu</label>
        <input type="password" name="confirm_password" id="confirm_password" placeholder="Nhập lại mật khẩu">
        <span class="auth-error" id="confirmError"></span><br><br>
        <button name="reset">Đổi mật khẩu</button>

    </form>
    <script src="../js/script.js"></script>
</body>

</html>