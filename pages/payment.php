<?php
require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

$order_id = (int)($_GET['order_id'] ?? 0);
$reference = $_GET['ref'] ?? '';
$user_id = (int)$_SESSION['user_id'];
$orderData = null;
$from_waiting = false;

if ($order_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();
    $orderData = $stmt->get_result()->fetch_assoc();
} elseif (!empty($reference)) {
    $stmt = $conn->prepare("SELECT * FROM payment_waiting WHERE reference = ? LIMIT 1");
    $stmt->bind_param("s", $reference);
    $stmt->execute();
    $waiting = $stmt->get_result()->fetch_assoc();
    if ($waiting) {
        $orderData = json_decode($waiting['order_data'], true);
        $from_waiting = true;
    }
}

if (!$orderData) {
    header("Location: " . BASE_URL . "index.php");
    exit();
}

$page_css = "checkout.css";
include("../includes/header.php");

$amount = (int)$orderData['total'];
$payment_method = $orderData['payment_method'];
$payment_status = $from_waiting ? 'unpaid' : ($orderData['payment_status'] ?? 'unpaid');
?>

<div class="checkout-page">
    <div class="checkout-wrapper" style="max-width: 900px; margin: 0 auto; padding: 40px 20px;">
        
        <?php if ($payment_status === 'paid'): ?>
            <div class="section-card" style="text-align: center; padding: 50px 30px; border-radius: 15px;">
                <div style="font-size: 80px; margin-bottom: 25px; color: #28a745;">✅</div>
                <h2 style="color: #333; margin-bottom: 15px; font-size: 28px;">Thanh toán thành công!</h2>
                <p style="color: #666; font-size: 16px; margin-bottom: 30px;">Cảm ơn bạn, đơn hàng #<?= $order_id ?> đã được xác nhận thanh toán.</p>
                <div style="display: flex; gap: 15px; justify-content: center;">
                    <a href="<?= BASE_URL ?>index.php" class="btn btn-outline">Tiếp tục mua sắm</a>
                    <a href="<?= BASE_URL ?>pages/orders.php" class="btn btn-primary">Xem đơn hàng</a>
                </div>
            </div>
        <?php else: ?>

            <div class="checkout-heading" style="text-align: center; margin-bottom: 30px;">
                <h2>💳 Thanh toán đơn hàng <?= $from_waiting ? "mới" : "#$order_id" ?></h2>
                <p>Vui lòng hoàn tất thanh toán để chúng tôi xử lý đơn hàng của bạn</p>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
                <!-- Cột trái: Thông tin đơn hàng -->
                <div class="section-card">
                    <h3 style="margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px;">📋 Thông tin đơn hàng</h3>
                    <div style="line-height: 2.2; color: #555;">
                        <p><strong>Người nhận:</strong> <?= htmlspecialchars($orderData['name'] ?? '') ?></p>
                        <p><strong>Số điện thoại:</strong> <?= htmlspecialchars($orderData['phone'] ?? '') ?></p>
                        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($orderData['address'] ?? '') ?></p>
                        <p><strong>Phương thức:</strong> 
                            <?php 
                                if($payment_method == 'cod') echo 'Tiền mặt (COD)';
                                elseif($payment_method == 'bank_transfer') echo 'Chuyển khoản ngân hàng';
                                else echo 'PayPal giả lập';
                            ?>
                        </p>
                        <div style="margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd;">
                            <p style="font-size: 20px; color: #333;"><strong>Tổng cộng:</strong> 
                                <span style="color: var(--primary-color); font-weight: 800;"><?= number_format($amount, 0, ',', '.') ?>đ</span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Cột phải: Hướng dẫn thanh toán -->
                <div class="section-card">
                    <?php if ($payment_method === 'cod'): ?>
                        <div style="text-align: center; padding: 20px 0;">
                            <div style="font-size: 60px; margin-bottom: 15px;">🚚</div>
                            <h3 style="color: #28a745;">Thanh toán khi nhận hàng</h3>
                            <p style="color: #666; margin-top: 10px;">Bạn sẽ thanh toán số tiền <strong><?= number_format($amount, 0, ',', '.') ?>đ</strong> cho nhân viên giao hàng khi nhận được sản phẩm.</p>
                            <button id="btn-confirm-cod" class="btn btn-primary" style="margin-top: 25px; width: 100%; background: #28a745; border:none;" onclick="confirmCOD()">Xác nhận đặt hàng</button>
                        </div>
                    <?php else: ?>
                        <h3 style="margin-bottom: 15px; color: #00468c;">🏦 Thông tin chuyển khoản</h3>
                        
                        <!-- Thêm mã QR VietQR -->
                        <div style="text-align: center; margin-bottom: 20px; background: #fff; padding: 15px; border-radius: 12px; border: 1px solid #eee; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
                            <p style="font-size: 13px; color: #666; margin-bottom: 10px;">Quét mã bằng App Ngân hàng để thanh toán nhanh</p>
                            <img src="https://img.vietqr.io/image/MB-0792302274-compact2.png?amount=<?= $amount ?>&addInfo=ORDER%20<?= $reference ?>&accountName=LE%20HUU%20BANG" 
                                 alt="QR Thanh toán" 
                                 style="max-width: 200px; height: auto; border: 1px solid #f0f0f0; border-radius: 8px;">
                        </div>

                        <div style="background: #f0f7ff; padding: 20px; border-radius: 10px; border-left: 5px solid #00468c; margin-bottom: 20px;">
                            <p style="margin-bottom: 8px;"><strong>Ngân hàng:</strong> <span style="color:#00468c">MB BANK (Quân Đội)</span></p>
                            <p style="margin-bottom: 8px;"><strong>Chủ tài khoản:</strong> <span style="color:#333">LE HUU BANG</span></p>
                            <p style="margin-bottom: 8px;"><strong>Số tài khoản:</strong> <span style="color:#d32f2f; font-weight:bold; font-size: 18px;">0792302274</span></p>
                            <p style="margin-bottom: 8px;"><strong>Số tiền:</strong> <span style="color:#d32f2f; font-weight:bold;"><?= number_format($amount, 0, ',', '.') ?>đ</span></p>
                            <p style="margin-bottom: 0;"><strong>Nội dung:</strong> <span style="background: #fff; padding: 2px 8px; border: 1px solid #ccc; font-weight: bold; color: #000;">ORDER <?= $reference ?: $order_id ?></span></p>
                        </div>

                        <div style="margin-top: 20px;">
                            <label style="display: block; margin-bottom: 8px; font-weight: bold; font-size: 14px; color: #555;">Tải lên ảnh minh chứng chuyển khoản:</label>
                            <div style="border: 2px dashed #ccc; padding: 15px; text-align: center; border-radius: 8px; cursor: pointer; background: #fafafa;" onclick="document.getElementById('proof-img').click()">
                                <input type="file" id="proof-img" style="display:none;" accept="image/*" onchange="previewImage(this)">
                                <div id="preview-container">
                                    <span style="font-size: 30px; color: #aaa;">📷</span>
                                    <p style="font-size: 13px; color: #888; margin-top: 5px;">Bấm để chọn ảnh từ thiết bị</p>
                                </div>
                            </div>
                        </div>

                        <button id="btn-confirm-payment" class="btn btn-primary" style="width: 100%; margin-top: 25px; padding: 15px; font-size: 16px; background: #28a745; border: none; border-radius: 8px;" onclick="confirmPayment()">
                            Đã thanh toán
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('preview-container').innerHTML = `
                <img src="${e.target.result}" style="max-width: 100%; max-height: 150px; border-radius: 5px;">
                <p style="font-size: 12px; color: #28a745; margin-top: 5px;">Đã chọn ảnh!</p>
            `;
        }
        reader.readAsDataURL(input.files[0]);
    }
}

function confirmCOD() {
    const btn = document.getElementById('btn-confirm-cod');
    const reference = "<?= $reference ?>";
    
    btn.disabled = true;
    btn.innerHTML = "⏳ Đang xử lý...";

    fetch("<?= BASE_URL ?>api/confirm_cod_order.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ reference: reference })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Đặt hàng thành công!");
            window.location.href = "<?= BASE_URL ?>pages/orders.php";
        } else {
            alert(data.message || "Có lỗi xảy ra");
            btn.disabled = false;
            btn.innerHTML = "Xác nhận đặt hàng";
        }
    })
    .catch(() => {
        alert("Lỗi kết nối máy chủ");
        btn.disabled = false;
        btn.innerHTML = "Xác nhận đặt hàng";
    });
}

function confirmPayment() {
    const btn = document.getElementById('btn-confirm-payment');
    const orderId = "<?= $order_id ?>";
    const reference = "<?= $reference ?>";
    
    btn.disabled = true;
    btn.innerHTML = "⏳ Đang xử lý...";

    fetch("<?= BASE_URL ?>api/comfirm_payment.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ order_id: orderId, reference: reference })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Xác nhận thanh toán thành công!");
            // Chuyển hướng sang trang thành công (có dấu tích) bằng cách dùng order_id mới
            window.location.href = "<?= BASE_URL ?>pages/payment.php?order_id=" + data.order_id;
        } else {
            alert(data.message || "Có lỗi xảy ra");
            btn.disabled = false;
            btn.innerHTML = "Đã thanh toán";
        }
    })
    .catch(() => {
        alert("Lỗi kết nối máy chủ");
        btn.disabled = false;
        btn.innerHTML = "Đã thanh toán";
    });
}
</script>

<?php include("../includes/footer.php"); ?>
