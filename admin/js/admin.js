const ctx = document.getElementById("chart");
fetch("api/revenue.php")
  .then((res) => res.json())
  .then((data) => {
    const labels = data.map((i) => i.day);
    const values = data.map((i) => i.revenue);

    new Chart(document.getElementById("chart"), {
      type: "line",
      data: {
        labels: labels,
        datasets: [
          {
            label: "Doanh thu",
            data: values,
            tension: 0.3,
          },
        ],
      },
    });
    // new Chart(ctx, {
    //   type: 'line',
    //   data: {
    //     labels: ['T2','T3','T4','T5','T6','T7','CN'],
    //     datasets: [{
    //       label: 'Doanh thu',
    //       data: [100,200,150,300,250,400,350],
    //       tension: 0.3
    //     }]
    //   }
    // });
  });
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
