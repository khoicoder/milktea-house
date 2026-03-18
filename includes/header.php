<?php require_once(__DIR__ . "/../config/config.php");?>


<?php
$currentUser = null;
$cartCount = 0;

if(isset($_SESSION['cart'])){
    $cartCount = array_sum($_SESSION['cart']);
}

if(isset($_SESSION['user_id'])){
    $uid = (int)$_SESSION['user_id'];
    $stmt = mysqli_prepare($conn,"SELECT id,username,display_name,avatar,role FROM users WHERE id=?");
    mysqli_stmt_bind_param($stmt,"i",$uid);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);
    $currentUser = mysqli_fetch_assoc($result);
}

// TÍNH TỔNG SỐ LƯỢNG SẢN PHẨM TRONG GIỎ HÀNG
$cart_count = 0;
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $cart_count = array_sum($_SESSION['cart']);
}
?>

<?php
$currentUser = null;

if(isset($_SESSION['user_id'])){

$uid = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn,"SELECT id,username,display_name,avatar,role FROM users WHERE id=?");
mysqli_stmt_bind_param($stmt,"i",$uid);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$currentUser = mysqli_fetch_assoc($result);

}


?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>MilkTea House 5aem</title>


<link rel="stylesheet" href="<?=BASE_URL?>css/base.css">
<link rel="stylesheet" href="<?=BASE_URL?>css/layout.css">

<?php
if(isset($page_css)){
echo '<link rel="stylesheet" href="'.BASE_URL.'css/' . $page_css . '">';
}
?>
<script src="<?= BASE_URL ?>js/script.js"></script>

</head>

<body>

<header class="header">

<div class="logo">
<a href="<?= BASE_URL ?>index.php">MilkTea House 5aem</a>
</div>

<nav class="menu">

<a href="<?= BASE_URL ?>index.php">Trang chủ</a>
<a href="<?= BASE_URL ?>pages/category.php?id=1">Trà sữa</a>
<a href="<?= BASE_URL ?>pages/category.php?id=2">Trà trái cây</a>
<a href="<?= BASE_URL ?>pages/category.php?id=3">Đá xay</a>
<a href="<?= BASE_URL ?>pages/category.php?id=4">Topping</a>

<a href="<?= BASE_URL ?>pages/cart.php" onclick="return checkLogin()">
    Giỏ hàng
        (<span id="cart-count"><?= $cartCount ?></span>)
</a>

</nav>

<div class="header-right">

<?php if(!$currentUser): ?>

<!-- Guest -->

<a href="<?= BASE_URL ?>pages/login.php" class="btn">Đăng nhập</a>
<a href="<?= BASE_URL ?>pages/register.php" class="btn">Đăng ký</a>

<?php else: ?>

<!-- Logged user -->

<div class="user-menu">

<img
src="<?= $currentUser['avatar'] ? BASE_URL.'uploads/'.$currentUser['avatar'] : BASE_URL.'images/user.jpg' ?>"
class="avatar"
onclick="toggleUserMenu()"
>

<div class="user-dropdown" id="userDropdown">

<p class="user-name">
Xin chào, <?= htmlspecialchars($currentUser['display_name'] ?: $currentUser['username']) ?>
</p>

<a href="<?= BASE_URL ?>pages/profile.php">Tài khoản</a>
<a href="<?= BASE_URL ?>pages/orders.php">Đơn hàng</a>

<?php if($currentUser['role'] === 'admin'): ?>
<a href="<?= BASE_URL ?>admin/dashboard.php">Admin Dashboard</a>
<?php endif; ?>

<a href="<?= BASE_URL ?>pages/logout.php">Đăng xuất</a>

</div>

</div>

<?php endif; ?>

</div>

</header>
<script>
 window.BASE_URL = "<?php echo rtrim(BASE_URL, '/'); ?>/";
 window.isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; 
 ?>;

function checkLogin() {

  if (!isLoggedIn) {

    alert("Vui lòng đăng nhập để xem giỏ hàng");

    window.location.href = window.BASE_URL + "pages/login.php";

    return false;
  }

  return true;
}

</script>