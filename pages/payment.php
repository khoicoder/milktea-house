<?php
require_once(__DIR__ . "/../config/config.php");

if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "pages/login.php");
    exit();
}

$order_id = (int)($_GET['order_id'] ?? 0);
$user_id  = (int)$_SESSION['user_id'];

if ($order_id <= 0) {
    die("Order ID không hợp lệ.");
}

// lấy đơn hàng
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ? LIMIT 1");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$res = $stmt->get_result();
$orderData = $res->fetch_assoc();

if (!$orderData) {
    die("Không tìm thấy đơn hàng.");
}

//  check expire realtime
if (
    $orderData['payment_status'] === 'pending' &&
    strtotime($orderData['payment_expires_at']) < time()
) {
    $conn->query("
        UPDATE orders 
        SET payment_status = 'expired',
            status = 'cancelled',
            expired_at = NOW()
        WHERE id = {$orderData['id']}
    ");

    $orderData['payment_status'] = 'expired';
}

$page_css = "checkout.css";
include("../includes/header.php");
?>

<div class="checkout-page">
    <h2>💳 Thanh toán đơn hàng #<?= $orderData['id'] ?></h2>

    <div class="checkout-container">
        <div class="checkout-left">
            <h3>📦 Thông tin thanh toán</h3>

            <p><strong>Trạng thái:</strong> <?= htmlspecialchars($orderData['payment_status']) ?></p>
            <p><strong>Phương thức:</strong> <?= htmlspecialchars($orderData['payment_method'] ?? 'bank_transfer') ?></p>
            <p><strong>Tổng tiền:</strong> <?= number_format((float)($orderData['total'] ?? 0)) ?> VNĐ</p>
            <p><strong>Họ tên:</strong> <?= htmlspecialchars($orderData['name'] ?? '') ?></p>
            <p><strong>SĐT:</strong> <?= htmlspecialchars($orderData['phone'] ?? '') ?></p>
            <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($orderData['address'] ?? '') ?></p>
            <p><strong>Ghi chú:</strong> <?= nl2br(htmlspecialchars($orderData['note'] ?? '')) ?></p>

            <hr>

            <p><strong>Ngân hàng:</strong> MB Bank</p>
            <p><strong>Số tài khoản:</strong> 123456789</p>
            <p><strong>Chủ tài khoản:</strong> LE MINH KHOI</p>
            <p><strong>Nội dung chuyển khoản:</strong> ORDER<?= (int)$orderData['id'] ?></p>

            <p>
                <strong>Hạn thanh toán:</strong>
                <span id="expire-time"><?= htmlspecialchars($orderData['payment_expires_at'] ?? '') ?></span>
            </p>
        </div>

        <div class="checkout-right" style="text-align:center;">
            <h3>🔳 Quét QR để thanh toán</h3>
<img src="<?="https://img.vietqr.io/image/" .BANK_CODE . "-" . BANK_ACCOUNT . "-compact.png?amount=".$orderData['total']. "&addInfo=ORDER".$orderData['id']
?>">
            <div class="total-box">
                Cần thanh toán: <strong><?= number_format((float)($orderData['total'] ?? 0)) ?>đ</strong>
            </div>

            <?php if ($orderData['payment_status'] === 'pending'): ?>
                <button class="btn-order" onclick="confirmPaid()">Tôi đã thanh toán</button>
                <?php elseif ($orderData['payment_status'] === 'expired'): ?>
                <p style="color:red;"><strong>Đơn đã hết hạn.</strong></p>
            <?php else: ?>
        <p style="color:green;"><strong>Đã thanh toán.</strong></p>
<?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

<script>
const now = new Date().getTime();
const diff = expireTime - now;
const expireTime = new Date("<?= $orderData['payment_expires_at'] ?>").getTime();
if (diff <= 0) {
    el.innerHTML = "Đã hết hạn";
    location.reload(); 
}
const el = document.getElementById("expire-time");

function updateCountdown() {
        now = new Date().getTime();
        diff = expireTime - now;

    if (diff <= 0) {
        el.innerHTML = "Đã hết hạn";
        location.reload();
        return;
    }

    const m = Math.floor(diff / 60000);
    const s = Math.floor((diff % 60000) / 1000);

    el.innerHTML = m + "m " + s + "s";
}
setInterval(updateCountdown, 10000);
updateCountdown();
</script>

<script>
const qrContent = <?= json_encode($orderData['qr_content'] ?? '') ?>;

if (qrContent) {
    new QRCode(document.getElementById("qr-wrap"), {
        text: qrContent,
        width: 220,
        height: 220
    });
} else {
    document.getElementById("qr-wrap").innerHTML = "<p>Không có dữ liệu QR.</p>";
}
const orderId = <?= json_encode($orderData['id'] ?? 0) ?>;

function confirmPaid() {
    fetch("<?= BASE_URL ?>api/confirm_payment.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        
        body: JSON.stringify({
            order_id: orderId
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert("Thanh toán thành công!");

            window.location.href = "<?= BASE_URL ?>pages/orders.php";
        } else {
            alert(data.message || "Có lỗi");
        }
    })
    .catch(() => alert("Lỗi kết .... server"));
}
</script>

<?php include("../includes/footer.php"); ?>