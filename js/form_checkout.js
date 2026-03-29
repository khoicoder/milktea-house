function goToCheckout() {
  const checked = document.querySelectorAll(".item-check:checked");

  if (checked.length === 0) {
    alert("Vui lòng chọn sản phẩm!");
    return;
  }

  const ids = [];
  checked.forEach((item) => ids.push(item.value));

  // Lấy mã giảm giá từ các biến global trong cart.js
  const coupon_id = typeof currentCouponId !== 'undefined' ? currentCouponId : null;
  const discount_amount = typeof currentDiscountAmount !== 'undefined' ? currentDiscountAmount : 0;

  fetch(BASE_URL + "api/save_checkout.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_ids: ids,
      coupon_id: coupon_id,
      discount_amount: discount_amount
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
    .catch(err => {
        console.error(err);
        alert("Có lỗi xảy ra khi chuyển sang thanh toán");
    });
}
