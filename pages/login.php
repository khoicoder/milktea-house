<?php
include("../config/config.php");

if(isset($_POST['login'])){

$email = $_POST['email'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE email='$email'";
$result = mysqli_query($conn,$sql);
        
$user = mysqli_fetch_assoc($result);

if($user && password_verify($password,$user['password'])){

$_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

header("Location: ../index.php");

}else{

echo "Sai email hoặc mật khẩu";

}

}
?>

<form method="POST">

<input type="email" name="email" placeholder="Email">

<input type="password" name="password" placeholder="Password">

<button name="login">Đăng nhập</button>

</form>