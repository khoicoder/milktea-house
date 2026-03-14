<?php
include("../config/config.php");
global $conn;
//biến
$username = "";
$email = "";
//biến chứa lỗi
$usernameError = "";
$emailError = "";
$passwordError = "";
if (isset($_POST["register"])) {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirm_password = $_POST["confirm_password"];
    if ($username == "" || $password == "" || $email == "" || $confirm_password == "") {
        echo "Vui lòng điền đủ thông tin";
        return;
    }
    if (strlen($username) < 3) {
        $usernameError = "Username phải ít nhất 3 ký tự";
        return;
    }

    if ($password != $confirm_password) {
        echo "Mật khẩu không khớp";
        exit;
    }
    if (
        strlen($password) < 6 ||
        !preg_match('/[A-Z]/', $password) ||
        !preg_match('/[0-9]/', $password) ||
        !preg_match('/[\W]/', $password)
    ) {

        $passwordError = "Mật khẩu chưa đủ mạnh";
        return;
    }

    $check = "SELECT * FROM users WHERE username = '$username' OR email = '$email' ";
    $result = mysqli_query($conn, $check);
    if (mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            if ($row['username'] == $username) {
                $usernameError =  "Username đã tồn tại<br>";
            }

            if ($row['email'] == $email) {
                $emailError =  "Email đã tồn tại<br>";
            }
        }
    }
    if ($usernameError == "" && $emailError == "" && $passwordError == "") {
        $hash_password = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users(username, email, password)
            VALUES('$username', '$email', '$hash_password')";
        mysqli_query($conn, $sql);
        $user_id = mysqli_insert_id($conn);
        $_SESSION['user_id'] = $user_id;
        $_SESSION['username'] = $username;
        $_SESSION['role'] = 'user';

        header("Location: ../index.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Đăng kí tài khoản</title>
</head>

<body>
    <div class="register-container">

        <h2>Đăng ký tài khoản</h2>

        <form method="POST" onsubmit="return validateForm()" novalidate>

            <label>Tên người dùng</label><br>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($username); ?>"><br>
            <span class="error" id="usernameError"><?php echo $usernameError ?? ''; ?></span><br>

            <label>Email</label><br>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>"><br>
            <span class="error" id="emailError"><?php echo $emailError ?? ''; ?></span><br>

            <label>Mật khẩu</label><br>
            <input type="password" name="password" id="password"><br>
            <span class="error" id="passwordError"><?php echo $passwordError ?? ''; ?></span><br>

            <label>Xác nhận lại mật khẩu</label><br>
            <input type="password" name="confirm_password" id="confirm_password"><br>
            <span class="error" id="confirmError"></span><br>

            <button type="submit" name="register">Đăng ký</button>

        </form>

        <div class="register-link">
            <a href="login.php">Đã có tài khoản?</a>
        </div>

    </div>
    <script src="../js/script.js"></script>
</body>

</html>