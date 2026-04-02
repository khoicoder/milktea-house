<?php
require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

$cart_items = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $product_id => $sizes) {
        $product_id = (int)$product_id;
        
        // Kiểm tra xem $sizes có phải mảng không (structure mới)
        // Nếu không phải mảng (là int), bỏ qua (cart cũ)
        if (!is_array($sizes)) {
            continue;
        }
        
        // Lấy thông tin sản phẩm
        $result = mysqli_query($conn, "SELECT id, name, image FROM products WHERE id = $product_id");
        $product = mysqli_fetch_assoc($result);
        
        if ($product) {
            // Lấy thông tin từng size cho sản phẩm này
            foreach ($sizes as $product_size_id => $quantity) {
                $product_size_id = (int)$product_size_id;
                $quantity = (int)$quantity;
                
                // Lấy giá và size name từ product_sizes
                $size_result = mysqli_query($conn, "
                    SELECT ps.id, ps.price, ps.stock, s.name as size_name
                    FROM product_sizes ps
                    JOIN sizes s ON ps.size_id = s.id
                    WHERE ps.id = $product_size_id
                ");
                $size_info = mysqli_fetch_assoc($size_result);
                
                if ($size_info) {
                    $cart_items[] = [
                        'id' => $product['id'],
                        'name' => $product['name'],
                        'image' => $product['image'],
                        'product_size_id' => $product_size_id,
                        'size_name' => $size_info['size_name'],
                        'price' => $size_info['price'],
                        'stock' => $size_info['stock'],
                        'quantity' => $quantity
                    ];
                }
            }
        }
    }
}

$page_css = "cart.css";
include("../includes/header.php");
?>

<div class="cart-page">
    <?php if (empty($cart_items)): ?>
        <div class="empty-cart">
            <span class="empty-cart-icon">🛒</span>
            <h3>Giỏ hàng trống</h3>
            <p>Bạn chưa có sản phẩm nào trong giỏ hàng.</p>
            <a href="<?= BASE_URL ?>index.php" class="btn-go-shop">Khám phá ngay</a>
        </div>
    <?php else: ?>
        <h2 class="cart-title">
            Giỏ hàng của tôi
            <span class="cart-count-badge"><?= count($cart_items) ?> sản phẩm</span>
        </h2>

        <div class="cart-layout">
            <!-- LEFT: Product list -->
            <div class="cart-left">
                <!-- Table -->
                <div class="cart-container">
                    <div class="cart-header">
                        <div class="col-check">
                            <input type="checkbox" id="check-all" onclick="toggleCheckAll(this)">
                        </div>
                        <div class="col-product">Sản phẩm</div>
                        <div class="col-price">Đơn giá</div>
                        <div class="col-qty">Số lượng</div>
                        <div class="col-subtotal">Thành tiền</div>
                        <div class="col-action"></div>
                    </div>

                    <div id="cart-item-list">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="cart-box item-row" id="item-row-<?= $item['product_size_id'] ?>">
                            <div class="col-check">
                                <input type="checkbox" class="item-check" value="<?= $item['product_size_id'] ?>" onclick="calculateTotal()">
                            </div>
                            <div class="col-product">
                                <img src="<?= BASE_URL ?>images/<?= $item['image'] ?>"
                                     class="product-img"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     onerror="this.onerror=null; this.src='<?= BASE_URL ?>images/default.jpg';">
                                <div>
                                    <a href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $item['id'] ?>" class="product-name">
                                        <?= htmlspecialchars($item['name']) ?>
                                    </a>
                                    <div style="font-size: 13px; color: #666; margin-top: 4px;">
                                        Size: <strong><?= htmlspecialchars($item['size_name']) ?></strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-price" id="price-<?= $item['product_size_id'] ?>" data-price="<?= $item['price'] ?>">
                                <?= number_format($item['price'], 0, ',', '.') ?>đ
                            </div>
                            <div class="col-qty">
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="updateQty(<?= $item['product_size_id'] ?>, -1)">−</button>
                                    <input type="text" class="qty-input" id="qty-<?= $item['product_size_id'] ?>"
                                           value="<?= $item['quantity'] ?>"
                                           data-stock="<?= $item['stock'] ?>" readonly>
                                    <button class="qty-btn" onclick="updateQty(<?= $item['product_size_id'] ?>, 1)">+</button>
                                </div>
                            </div>
                            <div class="col-subtotal" id="subtotal-<?= $item['product_size_id'] ?>">
                                <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                            </div>
                            <div class="col-action">
                                <button class="btn-delete" title="Xóa" onclick="removeItem(<?= $item['product_size_id'] ?>)">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                                         fill="none" stroke="currentColor" stroke-width="2.2"
                                         stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="3 6 5 6 21 6"></polyline>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                                        <path d="M10 11v6M14 11v6"></path>
                                        <path d="M9 6V4h6v2"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Batch bar -->
                <div class="cart-batch-bar">
                    <input type="checkbox" id="check-all-footer" onclick="toggleCheckAll(this)">
                    <label for="check-all-footer" style="cursor:pointer;">
                        Chọn tất cả (<span id="total-count-selected">0</span>)
                    </label>
                    <button class="btn-delete-batch" onclick="removeSelectedItems()">🗑 Xóa đã chọn</button>
                </div>

                <!-- Order note -->
                <div class="cart-note">
                    <div class="cart-note-label">
                        <span>📝 Ghi chú đơn hàng</span>
                        <span class="cart-note-counter">
                            <span id="note-char-count">0</span>/300
                        </span>
                    </div>
                    <textarea id="order-note" maxlength="300"
                              placeholder="Ví dụ: ít đường, không đá, thêm trân châu..."
                              rows="2"
                              oninput="updateNoteCounter(this)"></textarea>
                </div>
            </div><!-- /cart-left -->

            <!-- RIGHT: Summary panel -->
            <div class="cart-right">
                <div class="cart-summary">
                    <div class="summary-header">
                        🧾 Tóm tắt đơn hàng
                    </div>
                    <div class="summary-body">
                        <!-- Discount code -->
                        <div class="discount-area">
                            <input type="text" placeholder="Mã giảm giá..." id="discount-code">
                            <button onclick="applyDiscount()">Áp dụng</button>
                        </div>

                        <!-- Summary rows -->
                        <div class="summary-row">
                            <span class="label">Số sản phẩm đã chọn</span>
                            <span class="value"><span id="total-items">0</span> sản phẩm</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Tạm tính</span>
                            <span class="value" id="subtotal-display">0đ</span>
                        </div>
                        <div class="summary-row">
                            <span class="label">Giảm giá</span>
                            <span class="value" id="discount-display">—</span>
                        </div>

                        <!-- Total -->
                        <div class="summary-total">
                            <span class="total-label">Tổng thanh toán</span>
                            <span class="total-price" id="total-price-display">0đ</span>
                        </div>

                        <!-- Checkout -->
                        <button class="btn-checkout" onclick="alert('Button click!'); goToCheckout();">Đặt hàng ngay →</button>
                        <a href="<?= BASE_URL ?>index.php" class="btn-continue">← Tiếp tục mua sắm</a>
                    </div>
                </div>
            </div><!-- /cart-right -->
        </div><!-- /cart-layout -->
    <?php endif; ?>
</div>

<script>
    window.BASE_URL = '<?= rtrim(BASE_URL, '/'); ?>/';
</script>
<script src="<?= BASE_URL ?>js/cart.js"></script>
<script src="<?= BASE_URL ?>js/form_checkout.js"></script>
<script>
    console.log("BASE_URL:", window.BASE_URL);
    console.log("Số item trong cart_items PHP:", <?= count($cart_items) ?>);
    console.log("Số .item-row tìm được:", document.querySelectorAll(".item-row").length);
</script>

<?php include(__DIR__ . "/../includes/footer.php"); ?>
