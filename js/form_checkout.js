function goToCheckout() {
  console.log("=== goToCheckout được gọi ===");
  console.log("document có tồn tại:", !!document);

  // Lấy tất cả item rows
  let allItemRows = document.querySelectorAll(".item-row");
  console.log("Tìm với .item-row:", allItemRows.length);
  console.log("allItemRows elements:", allItemRows);

  // Fallback: thử selector khác
  if (allItemRows.length === 0) {
    allItemRows = document.querySelectorAll(".cart-box");
    console.log("Fallback .cart-box:", allItemRows.length);
    console.log("cart-box elements:", allItemRows);
  }

  // Fallback 2: thử #cart-item-list > div
  if (allItemRows.length === 0) {
    const container = document.getElementById("cart-item-list");
    console.log("Container #cart-item-list:", container);
    if (container) {
      allItemRows = container.querySelectorAll("div[id^='item-row-']");
      console.log("Fallback #cart-item-list div:", allItemRows.length);
    }
  }

  console.log("Cuối cùng tổng item:", allItemRows.length);
  console.log("Sắp kiểm tra if (allItemRows.length === 0)");

  if (allItemRows.length === 0) {
    console.log("ALERT: Giỏ hàng trống hoặc không tìm thấy item!");
    alert("Giỏ hàng trống hoặc không tìm thấy item!");
    return;
  }

  console.log("THÀNH CÔNG: Tìm được", allItemRows.length, "items");

  // Gửi product_size_id từ tất cả items
  const checkout_items = [];
  allItemRows.forEach((row) => {
    const rowId = row.id; // "item-row-71"
    console.log("Row ID:", rowId);
    if (rowId && rowId.startsWith("item-row-")) {
      const product_size_id = rowId.replace("item-row-", "");
      console.log("Thêm item:", product_size_id);
      checkout_items.push(product_size_id);
    }
  });

  console.log(
    "Tổng product_size_ids để checkout:",
    checkout_items.length,
    checkout_items,
  );

  // Lấy mã giảm giá từ các biến global trong cart.js
  const coupon_id =
    typeof currentCouponId !== "undefined" ? currentCouponId : null;
  const discount_amount =
    typeof currentDiscountAmount !== "undefined" ? currentDiscountAmount : 0;

  fetch(BASE_URL + "api/save_checkout.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_size_ids: checkout_items,
      coupon_id: coupon_id,
      discount_amount: discount_amount,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        window.location.href = BASE_URL + "pages/checkout.php";
      } else {
        alert(data.message);
      }
    })
    .catch((err) => {
      console.error(err);
      alert("Có lỗi xảy ra khi chuyển sang thanh toán");
    });
}
