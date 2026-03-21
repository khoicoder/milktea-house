<?php
require_once("../config/config.php");

// lấy product trước
if(isset($_GET['id'])){
    $id = (int)$_GET['id'];

    $sql = "SELECT * FROM products WHERE id=$id";
    $result = mysqli_query($conn,$sql);
    $product = mysqli_fetch_assoc($result);

    if(!$product){
        echo "Product not found";
        exit();
    }
}else{
    echo "Product not found";
    exit();
}

$currentUser = null;
if(isset($_SESSION['user_id'])){
    $uid = (int)$_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT username FROM users WHERE id=$uid");
    $currentUser = mysqli_fetch_assoc($res);
}

// submit comment
if(isset($_POST['submit_review'])){
    $product_id = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // nếu login → lấy username từ DB
    if($currentUser){
        $name = $currentUser['username'];
    } else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
    }

    if($product_id && $name && $rating){
        $sql = "INSERT INTO reviews(product_id, name, rating, comment) 
                VALUES($product_id, '$name', $rating, '$comment')";
        mysqli_query($conn, $sql);

        header("Location: product_detail.php?id=".$product_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php $page_css="product-detail.css";
        include("../includes/header.php");
    ?>

    <title><?php echo $product['name']; ?></title>

</head>

<body>

    <div class="product-detail">

        <div class="product-image">
        <img src="../images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        </div>

        <div class="product-info">

            <h1><?php echo $product['name']; ?></h1>

            <p class="price">
            <?php echo number_format($product['price'],0,",","."); ?> VND
            </p>

            <p class="desc">
            <?php echo $product['description']; ?>
            </p>

            <button class="buy-btn">Mua ngay</button>
            <!-- thêm vào giỏ hàng sẽ có ajax riêng, tránh reload trang mất luôn trạng thái -->
            <!-- // NOTE: dùng BASE_URL từ config để tránh lỗi đường dẫn khi deploy -->
            <button class="cart-btn" onclick="addCart(<?php echo $product['id']; ?>)">Thêm vào giỏ hàng</button>
        </div>

    </div>
    <div class="review-box">

    <h2>Đánh giá sản phẩm</h2>

    <form method="POST">
        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

       <?php if($currentUser): ?>
    <input type="text" value="<?= htmlspecialchars($currentUser['username']) ?>" disabled>
<?php else: ?>
    <input type="text" name="name" placeholder="Tên của bạn" required>
<?php endif; ?>

        <select name="rating" required>
            <option value="">Chọn số sao</option>
            <option value="5">⭐⭐⭐⭐⭐</option>
            <option value="4">⭐⭐⭐⭐</option>
            <option value="3">⭐⭐⭐</option>
            <option value="2">⭐⭐</option>
            <option value="1">⭐</option>
        </select>

        <textarea name="comment" placeholder="Nhập bình luận..." required></textarea>

        <button type="submit" name="submit_review">Gửi đánh giá</button>
    </form>

    <div class="review-list">

    <h3>Bình luận</h3>

    <?php
    $pid = $product['id'];
    $reviews = mysqli_query($conn, "SELECT * FROM reviews WHERE product_id=$pid ORDER BY id DESC");

    while($r = mysqli_fetch_assoc($reviews)){
    ?>

        <div class="review-item">
            <strong><?= htmlspecialchars($r['name']) ?></strong>
            <span>
                <?php 
                for($i=0;$i<$r['rating'];$i++) echo "⭐";
                ?>
            </span>
            <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
            <small><?= $r['created_at'] ?></small>
        </div>

    <?php } ?>

</div>

</div>

</body>
<div class="product-description">

    <h2>Mô tả sản phẩm</h2>

    <div class="desc">
        <?= nl2br($product['description']) ?>
    </div>

</div> 



<?php
    include("../includes/footer.php");

