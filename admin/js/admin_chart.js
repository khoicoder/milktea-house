let revenueChart = null;

function formatVND(value) {
  return Number(value || 0).toLocaleString("vi-VN") + " VNĐ";
}

function setRevenueTotal(value) {
  const totalEl = document.getElementById("revenueTotal");
  if (totalEl) {
    totalEl.textContent = formatVND(value);
  }
}

async function loadRevenueChart() {
  const form = document.getElementById("filterForm");
  if (!form) return;

  const params = new URLSearchParams(new FormData(form)).toString();

  try {
    const res = await fetch(`api/revenue.php?${params}`);
    const raw = await res.json();

    const items = Array.isArray(raw) ? raw : raw.data || raw.items || [];

    const totalRevenue = Array.isArray(raw)
      ? items.reduce((sum, i) => sum + Number(i.revenue || 0), 0)
      : Number(raw.total || 0);

    const labels = items.map((i) => i.label);
    const values = items.map((i) => Number(i.revenue || 0));

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
            label: "Doanh thu (VNĐ)",
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
                return formatVND(context.raw);
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

    setRevenueTotal(totalRevenue);
  } catch (err) {
    console.error("Không tải được chart:", err);
  }
}

document.addEventListener("DOMContentLoaded", () => {
  loadRevenueChart();

  const form = document.getElementById("filterForm");
  const chartType = document.getElementById("chartType");
  const period = document.getElementById("periodSelect");

  if (form) {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      loadRevenueChart();
    });
  }

  if (chartType) {
    chartType.addEventListener("change", loadRevenueChart);
  }

  if (period) {
    period.addEventListener("change", loadRevenueChart);
  }
});
