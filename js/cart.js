// Biến cờ khóa thao tác, chống user spam click liên tục gây lỗi server
let isUpdating = false;

// 1. Cập nhật số lượng
async function updateQty(id, change) {
    if (isUpdating) return;

    let qtyInput = document.getElementById('qty-' + id);
    let currentQty = parseInt(qtyInput.value);
    let maxStock = parseInt(qtyInput.getAttribute('data-stock')) || 999; 
    let newQty = currentQty + change;

    if (newQty < 1) return; 

    // Kiểm tra hàng trong kho
    if (newQty > maxStock) {
        alert("Sản phẩm này chỉ còn " + maxStock + " ly. Không thể thêm nữa!");
        return;
    }

    isUpdating = true;

    try {
        const res = await fetch(window.BASE_URL + "ajax/update_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}&quantity=${newQty}`
        });

        if (!res.ok) throw new Error("Server response error");
        const data = await res.json();

        if (data.status === 'success') {
            qtyInput.value = newQty;
            
            let price = parseFloat(document.getElementById('price-' + id).getAttribute('data-price'));
            let subtotal = price * newQty;
            document.getElementById('subtotal-' + id).innerText = subtotal.toLocaleString('vi-VN') + 'đ';
            
            if(typeof updateCartCount === 'function') updateCartCount(data.cart_count);
            calculateTotal();
        } else {
            console.error("Lỗi từ server:", data.message);
            alert(data.message);
        }
    } catch (error) {
        console.error("Lỗi kết nối:", error);
    } finally {
        isUpdating = false;
    }
}

// 2. Xóa 1 sản phẩm
async function removeItem(id) {
    if (!confirm("Bạn có chắc chắn muốn bỏ sản phẩm này?")) return;

    try {
        const res = await fetch(window.BASE_URL + "ajax/remove_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `id=${id}`
        });

        if (!res.ok) throw new Error("Server response error");
        const data = await res.json();

        if (data.status === "success") {
            const row = document.getElementById("item-row-" + id);
            if (row) row.remove();

            if (typeof updateCartCount === "function") updateCartCount(data.cart_count);
            calculateTotal();

            if (data.cart_count === 0) location.reload();
        }
    } catch (error) {
        console.error("Lỗi kết nối:", error);
        alert("Có lỗi xảy ra, vui lòng thử lại.");
    }
}

// 3. Xóa nhiều sản phẩm đã tick
async function removeSelectedItems() {
    let checkedItems = document.querySelectorAll('.item-check:checked');
    
    if (checkedItems.length === 0) {
        alert("Vui lòng chọn ít nhất một sản phẩm để xóa.");
        return;
    }

    if (!confirm("Bạn có chắc chắn muốn xóa " + checkedItems.length + " sản phẩm đã chọn khỏi giỏ hàng?")) return;

    let idsToDelete = [];
    checkedItems.forEach(cb => idsToDelete.push(cb.value));

    try {
        const res = await fetch(window.BASE_URL + "ajax/remove_multiple_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            // Đã bọc encodeURIComponent để chống lỗi format JSON khi gửi
            body: "ids=" + encodeURIComponent(JSON.stringify(idsToDelete)) 
        });

        if (!res.ok) throw new Error("Server response error");
        const data = await res.json();

        if(data.status === 'success') {
            idsToDelete.forEach(id => {
                let row = document.getElementById('item-row-' + id);
                if(row) row.remove();
            });
            
            if(typeof updateCartCount === 'function') updateCartCount(data.cart_count);
            calculateTotal();
            
            if (data.cart_count === 0) location.reload();
        }
    } catch (error) {
        console.error("Lỗi kết nối:", error);
    }
}

// 4. Chọn tất cả
function toggleCheckAll(source) {
    let isChecked = source.checked;
    
    // An toàn DOM: Kiểm tra element có tồn tại không trước khi đổi thuộc tính
    let checkAllTop = document.getElementById('check-all');
    let checkAllBottom = document.getElementById('check-all-footer');
    if (checkAllTop) checkAllTop.checked = isChecked;
    if (checkAllBottom) checkAllBottom.checked = isChecked;
    
    let checkboxes = document.querySelectorAll('.item-check');
    checkboxes.forEach(cb => cb.checked = isChecked);
    
    calculateTotal();
}

// 5. Tính tổng tiền
function calculateTotal() {
    let checkboxes = document.querySelectorAll('.item-check');
    let totalItems = 0;
    let totalPrice = 0;
    
    checkboxes.forEach(cb => {
        if (cb.checked) {
            totalItems++;
            let id = cb.value;
            let qtyInput = document.getElementById('qty-' + id);
            let priceElem = document.getElementById('price-' + id);
            
            // Chỉ tính nếu Element còn tồn tại trên DOM (tránh crash)
            if(qtyInput && priceElem) {
                let qty = parseInt(qtyInput.value);
                let price = parseFloat(priceElem.getAttribute('data-price'));
                totalPrice += (qty * price);
            }
        }
    });

    let isAllChecked = (checkboxes.length === totalItems) && (checkboxes.length > 0);
    
    let checkAllTop = document.getElementById('check-all');
    let checkAllBottom = document.getElementById('check-all-footer');
    if(checkAllTop) checkAllTop.checked = isAllChecked;
    if(checkAllBottom) checkAllBottom.checked = isAllChecked;

    let displayTotal = document.getElementById('total-price-display');
    let displayItems = document.getElementById('total-items');
    let displayCountSelected = document.getElementById('total-count-selected');

    if(displayTotal) displayTotal.innerText = totalPrice.toLocaleString('vi-VN') + 'đ';
    if(displayItems) displayItems.innerText = totalItems;
    if(displayCountSelected) displayCountSelected.innerText = totalItems;
}

// 6. Mã giảm giá
function applyDiscount() {
    alert("Chức năng mã giảm giá đang được xây dựng!");
}

// 7. Chuyển sang thanh toán
function checkout() {
    let checkedItems = document.querySelectorAll('.item-check:checked');
    if (checkedItems.length === 0) {
        alert("Bạn vẫn chưa chọn sản phẩm nào để mua.");
        return;
    }
    window.location.href = window.BASE_URL + 'pages/checkout.php';
}