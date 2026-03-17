<?php
require_once(__DIR__ . "/../config/config.php");

// Kiểm tra đăng nhập
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

// Lấy dữ liệu giỏ hàng
$cart_items = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    // Ép kiểu tất cả key về dạng số nguyên (int) để chống SQL Injection
    $safe_ids = array_map('intval', array_keys($_SESSION['cart']));
    $ids = implode(',', $safe_ids);
    
    $sql = "SELECT id, name, price, image, stock FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['quantity'] = $_SESSION['cart'][$row['id']];
        $cart_items[] = $row;
    }
}

$page_css="cart.css";
include("../includes/header.php");
?>
<div class="cart-page">
    <div class="cart-wrapper">
        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <p>Giỏ hàng của bạn đang trống.</p>
                <a href="<?= BASE_URL ?>index.php" class="btn-go-shop">MUA NGAY</a>
            </div>
        <?php else: ?>
            <div class="cart-box cart-header">
                <div class="col-check"><input type="checkbox" id="check-all" onclick="toggleCheckAll(this)"></div>
                <div class="col-product">Sản Phẩm</div>
                <div class="col-price">Đơn Giá</div>
                <div class="col-qty">Số Lượng</div>
                <div class="col-subtotal">Số Tiền</div>
                <div class="col-action">Thao Tác</div>
            </div>

            <div id="cart-item-list">
                <?php foreach ($cart_items as $item): ?>
                <div class="cart-box item-row" id="item-row-<?= $item['id'] ?>">
                    <div class="col-check">
                        <input type="checkbox" class="item-check" value="<?= $item['id'] ?>" onclick="calculateTotal()">
                    </div>
                    <div class="col-product">
                        <img src="<?= BASE_URL ?>images/<?= $item['image'] ?>" class="product-img" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.onerror=null; this.src='<?= BASE_URL ?>images/default.jpg';">
                        <a href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $item['id'] ?>" class="product-name"><?= htmlspecialchars($item['name']) ?></a>
                    </div>
                    <div class="col-price" id="price-<?= $item['id'] ?>" data-price="<?= $item['price'] ?>">
                        <?= number_format($item['price'], 0, ',', '.') ?>đ
                    </div>
                    <div class="col-qty">
                        <div class="qty-control">
                            <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">-</button>
                            <input type="text" class="qty-input" id="qty-<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" data-stock="<?= $item['stock'] ?>" readonly>
                            <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, 1)">+</button>
                        </div>
                    </div>
                    <div class="col-subtotal" id="subtotal-<?= $item['id'] ?>">
                        <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                    </div>
                    <div class="col-action">
                        <button class="btn-delete" onclick="removeItem(<?= $item['id'] ?>)">Xóa</button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="cart-footer">
                <div class="footer-left">
                    <input type="checkbox" id="check-all-footer" onclick="toggleCheckAll(this)"> 
                    <label for="check-all-footer" style="cursor: pointer;">Chọn Tất Cả (<span id="total-count-selected">0</span>)</label>
        
                    <button class="btn-delete-batch" onclick="removeSelectedItems()">Xóa</button>
                </div>
                
                <div class="footer-right">
                    <div class="discount-area">
                        <input type="text" placeholder="Nhập mã giảm giá..." id="discount-code">
                        <button onclick="applyDiscount()">Áp dụng</button>
                    </div>

                    <div class="total-text">
                        Tổng thanh toán (<span id="total-items">0</span> Sản phẩm): 
                        <span class="total-price" id="total-price-display">0đ</span>
                    </div>
                    <button class="btn-checkout" onclick="checkout()">Mua Hàng</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    window.BASE_URL = '<?= BASE_URL ?>';
</script>
<script src="<?= BASE_URL ?>js/cart.js"></script>

<?php include(__DIR__ . "/../includes/footer.php"); ?>