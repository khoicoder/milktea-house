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
    $ids = implode(',', array_keys($_SESSION['cart']));
    $sql = "SELECT id, name, price, image, stock FROM products WHERE id IN ($ids)";
    $result = mysqli_query($conn, $sql);
    
    while ($row = mysqli_fetch_assoc($result)) {
        $row['quantity'] = $_SESSION['cart'][$row['id']];
        $cart_items[] = $row;
    }
}

include(__DIR__ . "/../includes/header.php");
?>

<style>
/* CSS Giao diện Giỏ hàng - Đã đồng bộ màu với style.css */
.cart-wrapper { max-width: 1200px; margin: 20px auto; font-family: "Inter", "Segoe UI", sans-serif; color: #333;}
.cart-box { background: #fff; border-radius: 12px; padding: 15px 20px; margin-bottom: 15px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); display: flex; align-items: center; }
.cart-header { color: #888; font-size: 14px; font-weight: 600;}
.col-check { width: 5%; }
.col-product { width: 45%; display: flex; align-items: center; }
.col-price { width: 15%; text-align: center; color: #757575; }
.col-qty { width: 15%; text-align: center; }
.col-subtotal { width: 10%; text-align: center; color: #ff5a5f; font-weight: bold;} /* Đổi sang màu web */
.col-action { width: 10%; text-align: center; }

.product-img { width: 80px; height: 80px; object-fit: cover; border: 1px solid #e1e1e1; margin-right: 15px; border-radius: 8px;}
.product-name { font-size: 15px; line-height: 1.4; color: #333; text-decoration: none; font-weight: 500;}
.product-name:hover { color: #ff5a5f; }

/* Nút tăng giảm số lượng */
.qty-control { display: inline-flex; border: 1px solid #ddd; border-radius: 6px; overflow: hidden;}
.qty-btn { width: 32px; height: 32px; background: #f6f7fb; border: none; cursor: pointer; font-size: 16px; outline: none; color: #333; transition: 0.2s;}
.qty-btn:hover { background: #e0e0e0; }
.qty-input { width: 40px; height: 32px; border-left: 1px solid #ddd; border-right: 1px solid #ddd; border-top: none; border-bottom: none; text-align: center; outline: none; font-weight: 500;}

/* Nút xóa */
.btn-delete { color: #333; cursor: pointer; background: none; border: none; font-size: 14px; font-weight: 500; transition: 0.2s;}
.btn-delete:hover { color: #ff5a5f; }

/* Phần Footer thanh toán */
.cart-footer { position: sticky; bottom: 0; background: #fff; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 -4px 12px rgba(0,0,0,0.05); z-index: 100; border-radius: 12px;}
.footer-left { display: flex; align-items: center; gap: 20px; font-weight: 500;}
.footer-right { display: flex; align-items: center; gap: 20px; }

.discount-area { display: flex; align-items: center; gap: 10px; border-right: 1px solid #eee; padding-right: 20px;}
.discount-area input { padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; width: 200px; outline: none;}
.discount-area button { padding: 10px 18px; background: #ff5a5f; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: 600; transition: 0.2s;}
.discount-area button:hover { background: #e84a4f; }

.total-text { font-size: 16px; font-weight: 500;}
.total-price { font-size: 24px; color: #ff5a5f; font-weight: bold; margin-left: 10px;}
.btn-checkout { background: #ff5a5f; color: #fff; padding: 13px 40px; border: none; border-radius: 8px; font-size: 16px; font-weight: 600; cursor: pointer; transition: 0.2s;}
.btn-checkout:hover { background: #e84a4f; }

/* Giỏ hàng trống */
.empty-cart { text-align: center; padding: 80px 0; background: #fff; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);}
.empty-cart p { color: #888; margin-bottom: 25px; font-size: 16px; font-weight: 500;}
.btn-go-shop { background: #ff5a5f; color: #fff; padding: 12px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; display: inline-block; transition: 0.2s;}
.btn-go-shop:hover { background: #e84a4f; }
</style>

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
                    <img src="<?= BASE_URL ?>images/<?= $item['image'] ?>" class="product-img" alt="<?= htmlspecialchars($item['name']) ?>" onerror="this.src='https://via.placeholder.com/80'">
                    <a href="<?= BASE_URL ?>pages/product_detail.php?id=<?= $item['id'] ?>" class="product-name"><?= htmlspecialchars($item['name']) ?></a>
                </div>
                <div class="col-price" id="price-<?= $item['id'] ?>" data-price="<?= $item['price'] ?>">
                    <?= number_format($item['price'], 0, ',', '.') ?>đ
                </div>
                <div class="col-qty">
                    <div class="qty-control">
                        <button class="qty-btn" onclick="updateQty(<?= $item['id'] ?>, -1)">-</button>
                        <input type="text" class="qty-input" id="qty-<?= $item['id'] ?>" value="<?= $item['quantity'] ?>" readonly>
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
    
                <button onclick="removeSelectedItems()" style="background: none; border: none; font-size: 15px; color: #333; margin-left: 15px; cursor: pointer; font-weight: 500;">Xóa</button>
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

<script>
const BASE_URL = '<?= BASE_URL ?>';

// Hàm xử lý tăng giảm số lượng
function updateQty(id, change) {
    let qtyInput = document.getElementById('qty-' + id);
    let currentQty = parseInt(qtyInput.value);
    let newQty = currentQty + change;

    if (newQty < 1) return; 

    fetch(BASE_URL + "ajax/update_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}&quantity=${newQty}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            qtyInput.value = newQty;
            
            let price = parseFloat(document.getElementById('price-' + id).getAttribute('data-price'));
            let subtotal = price * newQty;
            document.getElementById('subtotal-' + id).innerText = subtotal.toLocaleString('vi-VN') + 'đ';
            
            if(typeof updateCartCount === 'function') updateCartCount(data.cart_count);
            calculateTotal();
        } else {
            console.error("Lỗi từ server:", data.message);
        }
    })
    .catch(error => console.error("Lỗi kết nối:", error));
}

// Xóa sản phẩm
function removeItem(id) {
    if (!confirm("Bạn có chắc chắn muốn bỏ sản phẩm này?")) return;

    fetch(BASE_URL + "ajax/remove_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `id=${id}`
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            document.getElementById('item-row-' + id).remove();
            if(typeof updateCartCount === 'function') updateCartCount(data.cart_count);
            calculateTotal();
            
            if (data.cart_count === 0) location.reload();
        }
    });
}

// Cập nhật hàm chọn tất cả
function toggleCheckAll(source) {
    // Lấy trạng thái của chính ô "Chọn tất cả" vừa được click (true hoặc false)
    let isChecked = source.checked;

    // Đồng bộ trạng thái cho cả 2 ô "Chọn tất cả" (trên và dưới)
    document.getElementById('check-all').checked = isChecked;
    document.getElementById('check-all-footer').checked = isChecked;
    
    // Áp dụng trạng thái đó cho tất cả các ô checkbox của sản phẩm
    let checkboxes = document.querySelectorAll('.item-check');
    checkboxes.forEach(cb => cb.checked = isChecked);
    
    // Tính lại tiền
    calculateTotal();
}

// Cập nhật hàm tính tổng tiền để đồng bộ ngược lại
function calculateTotal() {
    let checkboxes = document.querySelectorAll('.item-check');
    let checkedBoxes = document.querySelectorAll('.item-check:checked');
    let total = 0;
    
    // Nâng cấp: Nếu số ô sản phẩm được tick BẰNG với tổng số sản phẩm, tự động bật nút "Chọn tất cả"
    // Ngược lại, nếu có 1 ô bị bỏ tick, tự động tắt nút "Chọn tất cả"
    let isAllChecked = (checkboxes.length === checkedBoxes.length) && (checkboxes.length > 0);
    document.getElementById('check-all').checked = isAllChecked;
    document.getElementById('check-all-footer').checked = isAllChecked;

    // Tính tiền cho các sản phẩm đang được tick
    checkedBoxes.forEach(cb => {
        let id = cb.value;
        let qty = parseInt(document.getElementById('qty-' + id).value);
        let price = parseFloat(document.getElementById('price-' + id).getAttribute('data-price'));
        total += (qty * price);
    });

    // Hiển thị ra màn hình
    document.getElementById('total-price-display').innerText = total.toLocaleString('vi-VN') + 'đ';
    document.getElementById('total-items').innerText = checkedBoxes.length;
    document.getElementById('total-count-selected').innerText = checkedBoxes.length;
}

function applyDiscount() {
    alert("Chức năng mã giảm giá đang được xây dựng!");
}

function checkout() {
    let checkedItems = document.querySelectorAll('.item-check:checked');
    if (checkedItems.length === 0) {
        alert("Bạn vẫn chưa chọn sản phẩm nào để mua.");
        return;
    }
    window.location.href = BASE_URL + 'pages/checkout.php';
}

// Hàm xóa các sản phẩm đã được tick
function removeSelectedItems() {
    // 1. Tìm tất cả các ô checkbox sản phẩm đang được tick
    let checkedItems = document.querySelectorAll('.item-check:checked');
    
    if (checkedItems.length === 0) {
        alert("Vui lòng chọn ít nhất một sản phẩm để xóa.");
        return;
    }

    if (!confirm("Bạn có chắc chắn muốn xóa " + checkedItems.length + " sản phẩm đã chọn khỏi giỏ hàng?")) return;

    // 2. Gom tất cả ID của các sản phẩm đó vào một mảng
    let idsToDelete = [];
    checkedItems.forEach(cb => idsToDelete.push(cb.value));

    // 3. Gửi mảng ID này lên server để xử lý
    fetch(BASE_URL + "ajax/remove_multiple_cart.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        // Chuyển mảng thành chuỗi JSON để gửi qua HTTP Post
        body: "ids=" + JSON.stringify(idsToDelete) 
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            // Xóa các dòng HTML tương ứng với sản phẩm vừa xóa
            idsToDelete.forEach(id => {
                let row = document.getElementById('item-row-' + id);
                if(row) row.remove();
            });
            
            // Cập nhật lại số lượng trên giỏ hàng ở header
            if(typeof updateCartCount === 'function') updateCartCount(data.cart_count);
            
            // Tính lại tiền (bởi vì các ô đã chọn vừa bị xóa, nên hàm này sẽ tự động reset tiền về 0)
            calculateTotal();
            
            // Nếu xóa sạch giỏ hàng thì load lại trang
            if (data.cart_count === 0) location.reload();
        }
    })
    .catch(error => console.error("Lỗi kết nối:", error));
}



</script>

<?php include(__DIR__ . "/../includes/footer.php"); ?>