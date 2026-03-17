const ctx = document.getElementById("revenueChart");

new Chart(ctx, {
  type: "bar",
  data: {
    labels: ["T2", "T3", "T4", "T5", "T6", "T7", "CN"],
    datasets: [
      {
        label: "Doanh thu",
        data: [120, 190, 300, 250, 220, 310, 400],
      },
    ],
  },
});
