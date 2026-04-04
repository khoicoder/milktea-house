<?php
require_once("../config/config.php");

// lấy product trước
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $sql = "SELECT * FROM products WHERE id=$id";
    $result = mysqli_query($conn, $sql);
    $product = mysqli_fetch_assoc($result);

    if (!$product) {
        echo "Product not found";
        exit();
    }
} else {
    echo "Product not found";
    exit();
}

$currentUser = null;
if (isset($_SESSION['user_id'])) {
    $uid = (int)$_SESSION['user_id'];
    $res = mysqli_query($conn, "SELECT username FROM users WHERE id=$uid");
    $currentUser = mysqli_fetch_assoc($res);
}

// submit comment
if (isset($_POST['submit_review'])) {
    $product_id = (int)$_POST['product_id'];
    $rating = (int)$_POST['rating'];
    $comment = mysqli_real_escape_string($conn, $_POST['comment']);

    // nếu login → lấy username từ DB
    if ($currentUser) {
        $name = $currentUser['username'];
    } else {
        $name = mysqli_real_escape_string($conn, $_POST['name']);
    }

    if ($product_id && $name && $rating) {
        $sql = "INSERT INTO reviews(product_id, name, rating, comment) 
                VALUES($product_id, '$name', $rating, '$comment')";
        mysqli_query($conn, $sql);

        header("Location: product_detail.php?id=" . $product_id);
        exit();
    }
}

// lấy giá nhỏ nhất để hiển thị ban đầu
$res_min = mysqli_query($conn, "
    SELECT MIN(price) AS min_price 
    FROM product_sizes 
    WHERE product_id = {$product['id']}
");
$row_min = mysqli_fetch_assoc($res_min);
$min_price = (int)($row_min['min_price'] ?? 0);

// tính tổng stock
$res_stock = mysqli_query($conn, "
    SELECT SUM(stock) AS total_stock 
    FROM product_sizes 
    WHERE product_id = {$product['id']}
");
$row_stock = mysqli_fetch_assoc($res_stock);
$total_stock = (int)($row_stock['total_stock'] ?? 0);

// số lượng sản phẩm này trong giỏ
$cart_qty = 0;
if (isset($_SESSION['cart'][$product['id']])) {
    foreach ($_SESSION['cart'][$product['id']] as $qty) {
        $cart_qty += $qty;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <?php
    $page_css = "product-detail.css";
    include("../includes/header.php");
    ?>

    <title><?php echo htmlspecialchars($product['name']); ?></title>
</head>

<body>
<div class="product-detail-page">
    <div class="product-detail">

        <div class="product-image">
            <img src="../images/<?php echo htmlspecialchars($product['image']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
        </div>

        <div class="product-info">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>

            <!-- 1 chỗ giá duy nhất, tự đổi theo size -->
            <p id="selected-price-display" class="price" style="margin-top: 10px; font-size: 16px; font-weight: bold; color: #e74c3c;">
                <?php if ($min_price > 0): ?>
                    Từ <?php echo number_format($min_price, 0, ",", "."); ?> VND
                <?php else: ?>
                    Liên hệ
                <?php endif; ?>
            </p>

            <p class="desc">
                <?php echo nl2br(htmlspecialchars($product['description'])); ?>
            </p>

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

            <!-- chọn size -->
            <div class="size-options" style="margin-top: 15px;">
                <label for="size">Chọn size:</label>
                <select id="size" name="product_size_id" onchange="updatePriceDisplay()">
                    <option value="">-- Chọn size --</option>
                    <?php
                    $res_size = mysqli_query($conn, "
                        SELECT ps.id, s.name, ps.price 
                        FROM product_sizes ps
                        JOIN sizes s ON ps.size_id = s.id
                        WHERE ps.product_id = {$product['id']}
                    ");

                    while ($size = mysqli_fetch_assoc($res_size)) {
                        echo '<option value="' . $size['id'] . '" data-price="' . $size['price'] . '">'
                            . htmlspecialchars($size['name']) . ' - ' . number_format($size['price'], 0, ",", ".") . ' VND'
                            . '</option>';
                    }
                    ?>
                </select>
            </div>

            <button class="cart-btn"
                onclick="addCart(<?php echo $product['id']; ?>)"
                id="add-cart-btn"
                <?= ($total_stock <= 0) ? 'disabled style="background: #cbd5e0; cursor: not-allowed;"' : '' ?>
            >
                Thêm vào giỏ hàng
            </button>

            <div style="margin-top:10px;">
                🛒 Trong giỏ: <strong id="cart-qty"><?= $cart_qty ?></strong>
            </div>

            <div style="margin-top:20px;">
                <h3>📍 Vị trí cửa hàng</h3>

                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d6531.371375753194!2d105.76304651135766!3d10.041490840164549!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1svi!2s!4v1775233758620!5m2!1svi!2s"
                    width="100%"
                    height="500px"
                    style="border:0; border-radius:10px;"
                    allowfullscreen=""
                    loading="lazy">
                </iframe>

                <p style="margin-top:8px;">
                    <a href="https://maps.app.goo.gl/7GTWiuk5cQECPD1B7" target="_blank">
                        Xem trên Google Maps
                    </a>
                </p>
            </div>
        </div>
    </div>

    <div class="review-box">
        <h2>Đánh giá sản phẩm</h2>

        <form method="POST">
            <input type="hidden" name="product_id" value="<?= $product['id'] ?>">

            <?php if ($currentUser): ?>
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

            while ($r = mysqli_fetch_assoc($reviews)) {
            ?>
                <div class="review-item">
                    <strong><?= htmlspecialchars($r['name']) ?></strong>
                    <span>
                        <?php
                        for ($i = 0; $i < $r['rating']; $i++) echo "⭐";
                        ?>
                    </span>
                    <p><?= nl2br(htmlspecialchars($r['comment'])) ?></p>
                    <small><?= $r['created_at'] ?></small>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="product-description">
        <h2>Mô tả sản phẩm</h2>
        <div class="desc">
            <?= nl2br(htmlspecialchars($product['description'])) ?>
        </div>
    </div>
</div>

<?php include("../includes/footer.php"); ?>

<script>
    window.BASE_URL = '<?= rtrim(BASE_URL, '/'); ?>/';

    function updatePriceDisplay() {
        const sizeSelect = document.getElementById('size');
        const selectedOption = sizeSelect.options[sizeSelect.selectedIndex];
        const priceDisplay = document.getElementById('selected-price-display');
        const addCartBtn = document.getElementById('add-cart-btn');

        if (!selectedOption || selectedOption.value === '') {
            priceDisplay.textContent = 'Vui lòng chọn size';
            priceDisplay.style.color = '#e74c3c';
            addCartBtn.disabled = true;
            addCartBtn.style.background = '#cbd5e0';
            addCartBtn.style.cursor = 'not-allowed';
        } else {
            const price = parseInt(selectedOption.getAttribute('data-price')) || 0;
            priceDisplay.textContent = 'Giá: ' + price.toLocaleString('vi-VN') + ' VND';
            priceDisplay.style.color = '#27ae60';

            <?php if ($total_stock > 0): ?>
            addCartBtn.disabled = false;
            addCartBtn.style.background = '';
            addCartBtn.style.cursor = 'pointer';
            <?php endif; ?>
        }
    }

    async function addCart(productId) {
        const sizeSelect = document.getElementById('size');
        const productSizeId = sizeSelect.value;

        if (!productSizeId) {
            alert('Vui lòng chọn size trước khi thêm vào giỏ hàng');
            return;
        }

        try {
            const res = await fetch(window.BASE_URL + 'ajax/add_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `id=${productId}&product_size_id=${productSizeId}`,
            });

            if (!res.ok) {
                throw new Error(`Server response: ${res.status} ${res.statusText}`);
            }

            const responseText = await res.text();
            const data = JSON.parse(responseText);

            if (data.status === 'success') {
                alert(data.message);

                if (typeof updateCartCount === 'function') {
                    updateCartCount(data.cart_count);
                }

                if (data.product_qty !== undefined) {
                    const qtyEl = document.getElementById('cart-qty');
                    if (qtyEl) {
                        qtyEl.textContent = data.product_qty;
                    }
                }
            } else {
                alert(data.message);
            }
        } catch (error) {
            console.error('Lỗi kết nối:', error);
            alert('Lỗi: ' + error.message);
        }
    }

    window.addEventListener('DOMContentLoaded', function () {
        updatePriceDisplay();
    });
</script>
</body>
</html>