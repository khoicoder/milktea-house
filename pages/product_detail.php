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
<div class="product-detail-page">
    <div class="product-detail">

        <div class="product-image">
        <img src="../images/<?php echo $product['image']; ?>" alt="<?php echo $product['name']; ?>">
        </div>

        <div class="product-info">

            <h1><?php echo $product['name']; ?></h1>

            <p class="price">
            <?php
            $res_min = mysqli_query($conn, "
                SELECT MIN(price) as min_price 
                FROM product_sizes 
                WHERE product_id = {$product['id']}
");
            $row_min = mysqli_fetch_assoc($res_min);
?>

<p class="price">
    Từ <?php echo number_format($row_min['min_price'],0,",","."); ?> VND
</p>

            <p class="desc">
            <?php echo $product['description']; ?>
            </p>

            <div class="product-status" style="margin-bottom: 15px;">
                <?php
$res_stock = mysqli_query($conn, "
    SELECT SUM(stock) as total_stock 
    FROM product_sizes 
    WHERE product_id = {$product['id']}
");
$row_stock = mysqli_fetch_assoc($res_stock);
$total_stock = $row_stock['total_stock'];
?>

<div class="product-status" style="margin-bottom: 15px;">
<?php if ($total_stock > 0): ?>
    <span class="status-badge status-in">
        Còn hàng (<?= $total_stock ?>)
    </span>
<?php else: ?>
    <span class="status-badge status-out">
        Hết hàng
    </span>
<?php endif; ?>
</div>
                    
            <!-- thêm vào giỏ hàng sẽ có ajax riêng, tránh reload trang mất luôn trạng thái -->
            <!-- // NOTE: dùng BASE_URL từ config để tránh lỗi đường dẫn khi deploy -->
            <div class= "size-options" style="margin-top: 15px;">
                <label for="size">Chọn size:</label>
                <select id="size" name="product_size_id" onchange="updatePriceDisplay()">
                    <option value="">-- Chọn size --</option>
                    <?php
                    $res_size = mysqli_query($conn, "
                    SELECT ps.id, s.name, ps.price 
                    FROM product_sizes ps
                    JOIN sizes s ON ps.size_id = s.id
                    WHERE ps.product_id = {$product['id']}");
        while ($size = mysqli_fetch_assoc($res_size)) {
            echo 
            "<option value=\"{$size['id']}\" data-price=\"{$size['price']}\">
                {$size['name']} - " . number_format($size['price'],0,",",".") . " VND
            </option>";
}
?>
</select>
            </div>

            <!-- Hiển thị giá được chọn -->
            <div id="selected-price-display" style="margin-top: 10px; font-size: 16px; font-weight: bold; color: #e74c3c;">
                Vui lòng chọn size
            </div>

            <button class="cart-btn" onclick="addCart(<?php echo $product['id']; ?>)" id="add-cart-btn" <?= (isset($product['stock']) && $product['stock'] <= 0) ? 'disabled style="background: #cbd5e0; cursor: not-allowed;"' : '' ?>>Thêm vào giỏ hàng</button>
        </div>
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

</body>

<div class="product-description">

    <h2>Mô tả sản phẩm</h2>

    <div class="desc">
        <?= nl2br($product['description']) ?>
    </div>

</div> 
</div>



<?php
    include("../includes/footer.php");?>

<script>
    // Định nghĩa BASE_URL
    window.BASE_URL = '<?= rtrim(BASE_URL, '/'); ?>/';
</script>

<script>
// Hàm cập nhật hiển thị giá khi chọn size
function updatePriceDisplay() {
    const sizeSelect = document.getElementById('size');
    const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
    const priceDisplay = document.getElementById('selected-price-display');
    const addCartBtn = document.getElementById('add-cart-btn');
    
    if (selectedOption.value === '') {
        priceDisplay.textContent = 'Vui lòng chọn size';
        priceDisplay.style.color = '#e74c3c';
        addCartBtn.disabled = true;
        addCartBtn.style.background = '#cbd5e0';
        addCartBtn.style.cursor = 'not-allowed';
    } else {
        const price = parseInt(selectedOption.getAttribute('data-price'));
        priceDisplay.textContent = 'Giá: ' + price.toLocaleString('vi-VN') + ' VND';
        priceDisplay.style.color = '#27ae60';
        addCartBtn.disabled = false;
        addCartBtn.style.background = '';
        addCartBtn.style.cursor = 'pointer';
    }
}

// Ghi đè hàm addCart để gửi product_size_id
async function addCart(productId) {
    const sizeSelect = document.getElementById('size');
    const productSizeId = sizeSelect.value;
    
    if (!productSizeId) {
        alert('Vui lòng chọn size trước khi thêm vào giỏ hàng');
        return;
    }
    
    try {
        console.log('Fetching:', window.BASE_URL + 'ajax/add_cart.php');
        console.log('Body:', `id=${productId}&product_size_id=${productSizeId}`);
        
        const res = await fetch(window.BASE_URL + 'ajax/add_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `id=${productId}&product_size_id=${productSizeId}`,
        });

        if (!res.ok) {
            throw new Error(`Server response: ${res.status} ${res.statusText}`);
        }
        
        // Log response text trước khi parse JSON
        const responseText = await res.text();
        console.log('Response text:', responseText);
        
        // Parse JSON
        const data = JSON.parse(responseText);
        console.log('Parsed data:', data);

        if (data.status === 'success') {
            alert(data.message);
            if (typeof updateCartCount === 'function') {
                updateCartCount(data.cart_count);
            }
        } else {
            alert(data.message);
        }
    } catch (error) {
        console.error('Lỗi kết nối:', error);
        console.error('Stack:', error.stack);
        alert('Lỗi: ' + error.message);
    }
}

// Initialize: disable button nếu chưa chọn size
window.addEventListener('DOMContentLoaded', function() {
    updatePriceDisplay();
});
</script>
