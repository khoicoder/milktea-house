function goToCheckout() {
  const checked = document.querySelectorAll(".item-check:checked");

  if (
    checked.length === 0 &&
    !confirm("Bạn chưa chọn sản phẩm nào. Bạn có muốn tiếp tục?")
  ) {
    alert("Vui lòng chọn sản phẩm!");
    return;
  }

  const ids = [];
  checked.forEach((item) => ids.push(item.value));

  fetch(BASE_URL + "api/save_checkout.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_ids: ids,
    }),
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.success) {
        window.location.href = BASE_URL + "pages/checkout.php";
      } else {
        alert(data.message);
      }
    });
}
