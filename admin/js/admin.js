function deleteOrder(id) {
  if (!confirm("Xóa đơn hàng?")) return;

  fetch("../delete_order.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + id,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status === "success") {
        location.reload();
      }
    });
}
function updateOrder(id, status) {
  fetch("../api/order.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: `id=${id}&status=${status}`,
  }).then(() => location.reload());
}

function deleteUser(id) {
  if (!confirm("Xóa user này?")) return;

  fetch("../delete_user.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + id,
  })
    .then((res) => res.json())
    .then((data) => {
      location.reload();
    });
}

function deleteProduct(id) {
  if (!confirm("Bạn chắc chắn muốn xóa?")) return;

  fetch("delete_product.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: "id=" + id,
  })
    .then((res) => res.json())
    .then((data) => {
      if (data.status == "success") {
        location.reload();
      }
    });
}
