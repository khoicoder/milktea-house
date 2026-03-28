<?php
require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

$cart_items = [];
if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
    $safe_ids = array_map('intval', array_keys($_SESSION['cart']));
    $ids = implode(',', $safe_ids);

    $sql = "SELECT id, name, price, image, stock FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($result)) {
        $row['quantity'] = $_SESSION['cart'][$row['id']];
        $cart_items[] = $row;
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
                        <div class="cart-box item-row" id="item-row-<?= $item['id'] ?>">
                            <div class="col-check">
                                <input type="checkbox" class="item-check" value="<?= $item['id'] ?>" onclick="calculateTotal()">
                            </div>
                            <div class="col-product">
                                <img src="<?= BASE_URL ?>images/<?= $item['image'] ?>"
                                     class="product-img"
                                     alt="<?= htmlspecialchars($item['name']) ?>"
                                     onerror="this.onerror=null; this.src='<?= BASE_URL ?>images/default.jpg';">
                                <a href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $item['id'] ?>" class="product-name">
                                    <?= htmlspecialchars($item['name']) ?>
                                </a>
                            </div>
                            <div class="col-price" id="price-<?= $item['id'] ?>" data-price="<?= $item['price'] ?>">
                                <?= number_format($item['price'], 0, ',', '.') ?>đ
                            </div>
                            <div class="col-qty">
                                <div class="qty-control">
                                    <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">−</button>
                                    <input type="text" class="qty-input" id="qty-<?= $item['id'] ?>"
                                           value="<?= $item['quantity'] ?>"
                                           data-stock="<?= $item['stock'] ?>" readonly>
                                    <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, 1)">+</button>
                                </div>
                            </div>
                            <div class="col-subtotal" id="subtotal-<?= $item['id'] ?>">
                                <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                            </div>
                            <div class="col-action">
                                <button class="btn-delete" title="Xóa" onclick="removeItem(<?= $item['id'] ?>)">
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
                        <button class="btn-checkout" onclick="goToCheckout()">Đặt hàng ngay →</button>
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

<?php include(__DIR__ . "/../includes/footer.php"); ?>
