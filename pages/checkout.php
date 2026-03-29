<?php
require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

$uid = (int)$_SESSION['user_id'];

// Lấy thông tin user từ DB
$stmt = mysqli_prepare($conn, "SELECT display_name, phone, address FROM users WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $uid);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Decode phone & address (JSON array từ profile.php)
$user_phones = json_decode($user['phone'] ?? '[]', true);
if (!is_array($user_phones)) {
    $user_phones = ($user['phone'] ?? '') !== '' ? [$user['phone']] : [];
}
$user_phones = array_values(array_filter($user_phones));

$user_addresses = json_decode($user['address'] ?? '[]', true);
if (!is_array($user_addresses)) {
    $user_addresses = ($user['address'] ?? '') !== '' ? [$user['address']] : [];
}
$user_addresses = array_values(array_filter($user_addresses));

$display_name  = htmlspecialchars($user['display_name'] ?? '');
$first_phone   = htmlspecialchars($user_phones[0] ?? '');
$first_address = htmlspecialchars($user_addresses[0] ?? '');

// Lấy sản phẩm checkout
$checkout_ids = $_SESSION['checkout_items'] ?? [];
$cart_items   = [];

if (!empty($checkout_ids) && isset($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', $checkout_ids));
    $res = mysqli_query($conn, "SELECT id, name, price, image FROM products WHERE id IN ($ids)");
    while ($row = mysqli_fetch_assoc($res)) {
        $row['quantity'] = $_SESSION['cart'][$row['id']] ?? 0;
        $cart_items[] = $row;
    }
}

// Lấy coupon từ session (đã áp dụng ở trang cart)
$session_coupon_id = $_SESSION['coupon_id'] ?? null;
$session_discount = $_SESSION['discount_amount'] ?? 0;

$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$total = $subtotal - $session_discount;

$page_css = "checkout.css";
include("../includes/header.php");
?>

<div class="checkout-page">
  <div class="checkout-wrapper">

    <div class="checkout-heading">
      <h2>Xác nhận đặt hàng</h2>
      <p>Kiểm tra đơn hàng và điền thông tin giao hàng bên dưới</p>
    </div>

    <?php if (empty($cart_items)): ?>
      <div class="empty-cart-msg">
        <p>Không có sản phẩm nào để thanh toán.</p>
        <a href="<?= BASE_URL ?>index.php" class="btn-back">← Quay lại mua sắm</a>
      </div>
    <?php else: ?>

    <div class="checkout-container">

      <!-- CỘT TRÁI: Sản phẩm -->
      <div class="checkout-left">
        <div class="section-card">
          <div class="section-title">
            <span class="section-icon">🛒</span>
            <h3>Sản phẩm đặt hàng</h3>
          </div>

          <div class="item-list">
            <?php foreach ($cart_items as $item): ?>
              <div class="checkout-item">
                <div class="item-img-wrap">
                  <img
                    src="<?= BASE_URL ?>images/<?= htmlspecialchars($item['image'] ?? '') ?>"
                    alt="<?= htmlspecialchars($item['name']) ?>"
                    onerror="this.src='<?= BASE_URL ?>images/no-image.png'"
                  >
                </div>
                <div class="item-meta">
                  <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                  <div class="item-price-row">
                    <span class="item-unit"><?= number_format($item['price'], 0, ',', '.') ?>đ</span>
                    <span class="item-qty">× <?= $item['quantity'] ?></span>
                  </div>
                </div>
                <div class="item-subtotal">
                  <?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?>đ
                </div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="order-total">
            <span>Tạm tính</span>
            <strong><?= number_format($subtotal, 0, ',', '.') ?>đ</strong>
          </div>
        </div>
      </div>

      <!-- CỘT PHẢI: Form giao hàng -->
      <div class="checkout-right">
        <div class="section-card">
          <div class="section-title">
            <span class="section-icon">📋</span>
            <h3>Thông tin giao hàng</h3>
          </div>

          <!-- Họ tên -->
          <div class="form-group">
            <label for="name">Họ và tên</label>
            <input type="text" id="name" placeholder="Nhập họ tên" value="<?= $display_name ?>" required>
          </div>

          <!-- Số điện thoại -->
          <div class="form-group">
            <label>Số điện thoại</label>
            <div class="field-dropdown-wrap" id="phone-wrap">
              <div class="field-display" <?= count($user_phones) > 0 ? 'onclick="toggleDropdown(\'phone-wrap\')"' : '' ?>>
                <input type="tel" id="phone" placeholder="Nhập số điện thoại"
                       value="<?= $first_phone ?>" required <?= count($user_phones) > 0 ? 'readonly' : '' ?>>
                <?php if (count($user_phones) > 0): ?>
                  <span class="field-arrow">▾</span>
                <?php endif; ?>
              </div>

              <?php if (count($user_phones) >= 1): ?>
              <div class="field-dropdown" id="phone-dropdown">
                <?php foreach ($user_phones as $ph): ?>
                  <div class="dropdown-item" onclick="selectDropdown('phone-wrap', 'phone', '<?= $ph ?>', this)">
                    <span class="dropdown-check">✓</span>
                    <span><?= htmlspecialchars($ph) ?></span>
                  </div>
                <?php endforeach; ?>
                <div class="dropdown-item dropdown-custom-trigger" onclick="toggleCustomInput('phone-wrap', 'phone', this)">
                  <span class="dropdown-plus">＋</span>
                  <span>Nhập số khác...</span>
                </div>
                <div class="dropdown-custom-input" id="phone-custom-wrap" style="display:none;">
                  <input type="tel" id="phone-custom" placeholder="Nhập số điện thoại mới" oninput="applyCustomValue('phone', 'phone-custom')">
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Địa chỉ -->
          <div class="form-group">
            <label>Địa chỉ giao hàng</label>
            <div class="field-dropdown-wrap" id="address-wrap">
              <div class="field-display" <?= count($user_addresses) > 0 ? 'onclick="toggleDropdown(\'address-wrap\')"' : '' ?>>
                <input type="text" id="address" placeholder="Nhập địa chỉ"
                       value="<?= $first_address ?>" required <?= count($user_addresses) > 0 ? 'readonly' : '' ?>>
                <?php if (count($user_addresses) > 0): ?>
                  <span class="field-arrow">▾</span>
                <?php endif; ?>
              </div>

              <?php if (count($user_addresses) >= 1): ?>
              <div class="field-dropdown" id="address-dropdown">
                <?php foreach ($user_addresses as $addr): ?>
                  <div class="dropdown-item" onclick="selectDropdown('address-wrap', 'address', '<?= htmlspecialchars($addr, ENT_QUOTES) ?>', this)">
                    <span class="dropdown-check">✓</span>
                    <span><?= htmlspecialchars($addr) ?></span>
                  </div>
                <?php endforeach; ?>
                <div class="dropdown-item dropdown-custom-trigger" onclick="toggleCustomInput('address-wrap', 'address', this)">
                  <span class="dropdown-plus">＋</span>
                  <span>Nhập địa chỉ khác...</span>
                </div>
                <div class="dropdown-custom-input" id="address-custom-wrap" style="display:none;">
                  <input type="text" id="address-custom" placeholder="Nhập địa chỉ mới" oninput="applyCustomValue('address', 'address-custom')">
                </div>
              </div>
              <?php endif; ?>
            </div>
          </div>

          <!-- Ghi chú -->
          <div class="form-group">
            <label for="note">Ghi chú (tuỳ chọn)</label>
            <textarea id="note" placeholder="Ví dụ: Giao giờ hành chính..."></textarea>
          </div>

          <!-- Phương thức thanh toán -->
          <div class="form-group">
            <label for="payment_method">Phương thức thanh toán</label>
            <div class="select-wrap">
              <select id="payment_method">
                <option value="cod">💵 Tiền mặt khi nhận hàng (COD)</option>
                <option value="bank_transfer">🏦 Chuyển khoản ngân hàng</option>
                <option value="fake_paypal">💳 PayPal giả lập</option>
              </select>
            </div>
          </div>

          <!-- Confirm -->
          <div class="confirm-box">
            <div id="checkout-summary" style="margin-bottom: 15px; width: 100%;">
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; color: #666;">
                    <span>Tạm tính</span>
                    <span><?= number_format($subtotal, 0, ',', '.') ?>đ</span>
                </div>
                <?php if ($session_discount > 0): ?>
                <div style="display: flex; justify-content: space-between; margin-bottom: 5px; color: #ff5a5f;">
                    <span>Giảm giá</span>
                    <span>-<?= number_format($session_discount, 0, ',', '.') ?>đ</span>
                </div>
                <?php endif; ?>
            </div>
            <div class="confirm-total">
              <span>Tổng thanh toán</span>
              <strong><?= number_format($total, 0, ',', '.') ?>đ</strong>
            </div>
            <button class="btn-order" onclick="placeOrder()">Đặt hàng ngay →</button>
          </div>

        </div>
      </div>
    </div>

    <?php endif; ?>
  </div>
</div>

<script>
// Các biến từ PHP sang JS
const couponId = <?= json_encode($session_coupon_id) ?>;

function toggleDropdown(wrapId) {
  const wrap = document.getElementById(wrapId);
  const isOpen = wrap.classList.contains('open');
  document.querySelectorAll('.field-dropdown-wrap.open').forEach(w => w.classList.remove('open'));
  if (!isOpen) wrap.classList.add('open');
}

function selectDropdown(wrapId, inputId, value, el) {
  const input = document.getElementById(inputId);
  input.value = value;
  input.readOnly = true;
  const wrap = document.getElementById(wrapId);
  wrap.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
  el.classList.add('active');
  const customWrap = document.getElementById(inputId + '-custom-wrap');
  if (customWrap) customWrap.style.display = 'none';
  wrap.classList.remove('open');
}

function toggleCustomInput(wrapId, inputId, el) {
  const customWrap = document.getElementById(inputId + '-custom-wrap');
  const isHidden = customWrap.style.display === 'none';
  customWrap.style.display = isHidden ? 'block' : 'none';
  if (isHidden) {
    const customInput = document.getElementById(inputId + '-custom');
    customInput.value = '';
    customInput.focus();
    const wrap = document.getElementById(wrapId);
    wrap.querySelectorAll('.dropdown-item').forEach(item => item.classList.remove('active'));
    el.classList.add('active');
    document.getElementById(inputId).value = '';
    document.getElementById(inputId).readOnly = false;
  }
}

function applyCustomValue(inputId, customInputId) {
  const val = document.getElementById(customInputId).value;
  const mainInput = document.getElementById(inputId);
  mainInput.value = val;
  mainInput.readOnly = false;
}

document.addEventListener('click', function(e) {
  if (!e.target.closest('.field-dropdown-wrap')) {
    document.querySelectorAll('.field-dropdown-wrap.open').forEach(w => w.classList.remove('open'));
  }
});

function placeOrder() {
  const name = document.getElementById("name").value.trim();
  const phone = document.getElementById("phone").value.trim();
  const address = document.getElementById("address").value.trim();
  const note = document.getElementById("note").value.trim();
  const payment_method = document.getElementById("payment_method").value;

  if (!name || !phone || !address) {
    alert("Vui lòng nhập đầy đủ thông tin giao hàng");
    return;
  }

  fetch("<?= BASE_URL ?>api/create_order.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({ 
        name, phone, address, note, payment_method,
        coupon_id: couponId
    })
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      alert("Đơn hàng đã được tạo!");
      window.location.href = "<?= BASE_URL ?>pages/payment.php?ref=" + data.reference;
    } else {
      alert(data.message || "Có lỗi xảy ra");
    }
  })
  .catch(() => alert("Lỗi kết nối ......server/checkout"));
}
</script>

<?php include("../includes/footer.php"); ?>
