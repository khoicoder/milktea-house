<?php
include("../config/config.php");
global $conn;
if (isset($_POST['login'])) {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM users WHERE email='$email'";
    $result = mysqli_query($conn, $sql);

    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];

        header("Location: ../index.php");
        exit();
    } else {

        $error = "Sai email hoặc mật khẩu";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Đăng Nhập - Milktea House</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <h2>Đăng nhập Milktea House</h2>
    <?php
    if (isset($error)) {
        echo "<p style='color:red;'>$error</p>";
    }
    ?>
    <form method="post">
        <label>Email</label><br>
        <input type="email" name="email" required placeholder="Email đăng nhập"><br>
        <label>Mật khẩu</label><br>
        <input type="password" name="password" required placeholder="Mật khẩu"><br>

        <button type="submit" name="login">Đăng nhập</button>
    </form>
    <div class="login-link">
        <a href="forgotPassword.php">Quên mật khẩu?</a><br>
        <a href="register.php">Bạn chưa có tài khoản?</a>
    </div>
</body>

</html>