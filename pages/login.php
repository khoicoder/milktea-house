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
    
            <div class="password-wrapper">
                <input type="password" id="password" name="password" required placeholder="Mật khẩu">
                <span id="togglePassword" style="cursor: pointer;">👁</span>
            </div>

            <button type="submit" name="login">Đăng nhập</button>

        </form>

        <div class="auth-links">
            <a href="forgotPassword.php">Quên mật khẩu?</a>
            <a href="register.php">Bạn chưa có tài khoản?</a>
        </div>

    </div>

</div>

</body>
<script>
const toggle = document.getElementById("togglePassword");
const password = document.getElementById("password");

toggle.addEventListener("click", function () {
    const type = password.getAttribute("type") === "password" ? "text" : "password";
    password.setAttribute("type", type);

    // đổi icon (optional)
    this.textContent = type === "password" ? "👁" : "🙈";
});
</script>

</html>