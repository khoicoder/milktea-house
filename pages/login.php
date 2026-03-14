<?php
include("../config/config.php");

$page_css="auth.css";
if(isset($page_css)){
    echo '<link rel="stylesheet" href="'.BASE_URL.'css/' . $page_css . '">';
}


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

</head>

<body>

<div class="auth-container">

    <div class="auth-box">

        <h2>Đăng nhập Milktea House</h2>

        <?php
        if (isset($error)) {
            echo "<div class='auth-error'>$error</div>";
        }
        ?>

        <form method="post">

            <label>Email</label>
            <input type="email" name="email" required placeholder="Email đăng nhập">

            <label>Mật khẩu</label>
            <input type="password" name="password" required placeholder="Mật khẩu">

            <button type="submit" name="login">Đăng nhập</button>

        </form>

        <div class="auth-links">
            <a href="forgotPassword.php">Quên mật khẩu?</a>
            <a href="register.php">Bạn chưa có tài khoản?</a>
        </div>

    </div>

</div>

</body>

</html>