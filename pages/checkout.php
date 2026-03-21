<?php
require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

$checkout_ids = $_SESSION['checkout_items'] ?? [];
$cart_items = [];

if (!empty($checkout_ids) && isset($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', $checkout_ids));
    $sql = "SELECT id, name, price, image, stock, reserved_stock FROM products WHERE id IN ($ids)";
    $res = mysqli_query($conn, $sql);

    while ($row = mysqli_fetch_assoc($res)) {
        $row['quantity'] = $_SESSION['cart'][$row['id']] ?? 0;
        $cart_items[] = $row;
    }
}

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

$page_css = "checkout.css";
include("../includes/header.php");
?>

<div class="checkout-page">
    <h2>💳 Thanh toán</h2>

    <?php if (empty($cart_items)): ?>
        <p>Không có sản phẩm để thanh toán.</p>
    <?php else: ?>
    <div class="checkout-container">
        <div class="checkout-left">
            <h3>📦 Sản phẩm</h3>

            <?php foreach ($cart_items as $item): ?>
                <div class="checkout-item">
                    <img src="<?= BASE_URL ?>images/<?= $item['image'] ?>">
                    <div>
                        <div><?= htmlspecialchars($item['name']) ?></div>
                        <div><?= number_format($item['price']) ?>đ x <?= $item['quantity'] ?></div>
                    </div>
                    <div>
                        <?= number_format($item['price'] * $item['quantity']) ?>đ
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="checkout-right">
            <h3>🧾 Thông tin giao hàng</h3>

            <div class="form-group">
                <label>Họ tên</label>
                <input type="text" id="name" placeholder="Nhập họ tên" required>
            </div>

            <div class="form-group">
                <label>Số điện thoại</label>
                <input type="tel" id="phone" placeholder="Nhập số điện thoại" required>
            </div>

            <div class="form-group">
                <label>Địa chỉ</label>
                <input type="text" id="address" placeholder="Nhập địa chỉ" required>
            </div>

            <div class="form-group">
                <label>Ghi chú</label>
                <textarea id="note" placeholder="Nhập ghi chú (nếu có)"></textarea>
            </div>

            <div class="form-group">
                <label>Phương thức thanh toán</label>
                <select id="payment_method">
                    <option value="bank_transfer">Chuyển khoản ngân hàng</option>
                    <option value="fake_paypal">PayPal giả lập</option>
                </select>
            </div>

            <div class="total-box">
                Tổng tiền: <strong><?= number_format($total) ?>vnđ</strong>
            </div>

            <button class="btn-order" onclick="placeOrder() ">Xác nhận đặt hàng</button>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function placeOrder() {
    const name = document.getElementById("name").value.trim();
    const phone = document.getElementById("phone").value.trim();
    const address = document.getElementById("address").value.trim();
    const note = document.getElementById("note").value.trim();
    const payment_method = document.getElementById("payment_method").value;
    

    if (!name || !phone || !address) {
        alert("Vui lòng nhập đầy đủ thông tin");
        return ;
    }

    fetch("<?= BASE_URL ?>api/create_order.php", {
    method: "POST",
    headers: {
        "Content-Type": "application/json"
    },
    body: JSON.stringify({
        name,
        phone,
        address,
        note,
        payment_method
    })
})
.then(res => res.json())
.then(data => {
    console.log("DATA:", data);
    if(!data.order_id) {
        alert(data.message || "Có lỗi xảy ra");
        console.error(data);
        return;
    }

    if (data.success) {
        alert("Đơn hàng đã được tạo! Vui lòng thanh toán.");
        window.location.href = "<?= BASE_URL ?>pages/payment.php?order_id=" + data.order_id;
}})
.catch(err => {
    console.error(err);
    alert("Lỗi kết nối server");
});}
</script>

<?php include("../includes/footer.php"); ?>