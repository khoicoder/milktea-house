<?php
//------------------------------------
//PHP mailer 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';
//----------------------------------
require_once("../config/config.php");
$page_css = "auth.css";
if(isset($page_css)){
    echo '<link rel="stylesheet" href="'.BASE_URL.'css/' . $page_css . '">';
}
$message = "";
if (isset($_POST['forgot'])) {
    $email = $_POST['email'];
    // kiểm tra email nếu bị rỗng
    if (empty($email)) {
        echo $message = 'Nhập lại email của bạn';
    } else {
        //tìm người dùng
        $sql = "SELECT * FROM users WHERE email = '$email' ";
        $result = mysqli_query($conn, $sql);
        $users = mysqli_fetch_assoc($result);
        //nếu tồn tại thì tạo token
        if ($users) {
            $token = bin2hex(random_bytes(32));
            $expire = date("Y-m-d H:i:s", time() + 3600);
            $sql = "UPDATE users
        SET reset_token = '$token', reset_token_expire ='$expire'
        WHERE email='$email'";
            $result = mysqli_query($conn, $sql);
            // tạo link
            $link = BASE_URL . "pages/reset_password.php?token=$token";
            $mail = new PHPMailer(true);

            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;

                $mail->Username = 'nguyenhuynhkha0203@gmail.com'; // 👉 đổi thành gmail của bạn
                $mail->Password = 'oiak tedk rnry znyz'; // 👉 dán app password ở bước 1

                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                // người gửi
                $mail->setFrom('nguyenhuynhkha0203@gmail.com', 'Milk Tea House');

                // người nhận
                $mail->addAddress($email);

                // nội dung
                $mail->isHTML(true);
                $mail->Subject = 'Reset mật khẩu';
                $mail->Body = "
        <h3>Yêu cầu đặt lại mật khẩu</h3>
        <p>Click vào link bên dưới:</p>
        <a href='$link'>Reset Password</a>
    ";

                $mail->send();

                $message = "Đã gửi email thành công";
            } catch (Exception $e) {
                $message = "Lỗi gửi mail: {$mail->ErrorInfo}";
            }
            //echo $message =  "Link reset: $link <br> ";
        }
        echo $message =  "Đã gửi Email vui lòng check trong Email của bạn";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quen mat khau</title>
</head>

<body>
    <div class="auth-container">
        <div class="auth-box">
            <h2>Quên Mật Khẩu</h2>
            <form method="post">
                <input type="email" name="email" placeholder="Nhập email của bạn" value=" <?php echo $_POST['email'] ?? ''; ?>">
                <button name="forgot">Quên mật khẩu</button>
            </form>
        </div>
    </div>
</body>

</html>