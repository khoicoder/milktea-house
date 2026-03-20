let revenueChart = null;

function loadRevenueChart() {
  const form = document.getElementById("filterForm");
  if (!form) return;

  const params = new URLSearchParams(new FormData(form)).toString();

  fetch(`../api/revenue.php?${params}`)
    .then((res) => res.json())
    .then((data) => {
      const labels = data.map((i) => i.label);
      const values = data.map((i) => Number(i.revenue || 0));

      const canvas = document.getElementById("revenueChart");
      if (!canvas) return;

      if (revenueChart) {
        revenueChart.destroy();
      }

      const chartType = document.getElementById("chartType")?.value || "bar";

      revenueChart = new Chart(canvas, {
        type: chartType,
        data: {
          labels,
          datasets: [
            {
              label: "Doanh thu (VND)",
              data: values,
              borderWidth: 2,
              tension: 0.35,
            },
          ],
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          plugins: {
            legend: {
              display: true,
            },
            tooltip: {
              callbacks: {
                label: function (context) {
                  return Number(context.raw).toLocaleString("vi-VN") + "đ";
                },
              },
            },
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                callback: function (value) {
                  return Number(value).toLocaleString("vi-VN");
                },
              },
            },
          },
        },
      });
    })
    .catch((err) => {
      console.error("Không tải được chart:", err);
    });
}

document.addEventListener("DOMContentLoaded", () => {
  loadRevenueChart();

  const form = document.getElementById("filterForm");
  const chartType = document.getElementById("chartType");
  const period = document.getElementById("period");

  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      loadRevenueChart();
    });
  }

  if (chartType) {
    chartType.addEventListener("change", () => loadRevenueChart());
  }

  if (period) {
    period.addEventListener("change", () => loadRevenueChart());
  }
});
