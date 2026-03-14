<?php
require_once(__DIR__ . "/../config/config.php");


//QL Injection (mức độ: nguy hiểm)

// Đoạn này:

// $ids = implode(',', array_keys($_SESSION['cart']));
// $sql = "SELECT id, name, price, image, stock FROM products WHERE id IN ($ids)";

// Nếu session bị sửa hoặc inject, query có thể bị phá.

///BASE_URL bị khai báo nhiều lần
//placeholder image gây spam console
//calculateTotal() có thể crash  Nếu element bị xóa trước khi JS chạy = lỗi.
//removeSelectedItems gửi JSON chưa chuẩn

// Cải tiến: Tạo biến BASE_URL và isLoggedIn toàn cục để dùng trong JS
//updateQty() thiếu kiểm tra stock

// Hiện tại bạn chỉ check server.

// Nhưng client vẫn có thể spam nút + để tăng số lượng vượt stock trước khi server phản hồi. Cần disable nút + nếu đạt giới hạn stock.
//removeSelectedItems gửi JSON chưa chuẩn - nên gửi status và message để JS xử lý tốt hơn, thay vì chỉ gửi cart_count.

//gọi: document.getElementById
//      document.querySelectorAll rất nhiều lần. Nên cache lại nếu dùng nhiều lần trong cùng 1 hàm 

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