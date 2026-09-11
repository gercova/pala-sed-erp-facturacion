// Chart.js v4 (dashboard)
(function () {
  const el = document.getElementById("myBarChart");
  if (!el || typeof Chart === "undefined") return;

  const MONTHS = ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
  const fmt = (signo, v) => `${signo}${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

  $.ajax({
    url: urlReporteIngresos,
    type: "GET",
    dataType: "json",
    success: function (response) {
      const signo = response.signo || "S/ ";
      const ingresosMensuales = Array.isArray(response.ingresos) ? response.ingresos.map((v) => Number(v) || 0) : [];

      new Chart(el.getContext("2d"), {
        type: "bar",
        data: {
          labels: MONTHS,
          datasets: [
            {
              label: "Ingresos",
              data: ingresosMensuales,
              backgroundColor: "rgba(0, 97, 242, 0.92)",
              borderRadius: 8,
              maxBarThickness: 26,
            },
          ],
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (ctx) => `Ingresos: ${fmt(signo, ctx.parsed.y)}`,
              },
            },
          },
          scales: {
            x: {
              grid: { display: false },
              ticks: { maxRotation: 0 },
            },
            y: {
              beginAtZero: true,
              ticks: {
                callback: (value) => fmt(signo, value),
              },
              grid: { color: "rgba(33, 40, 50, 0.06)" },
            },
          },
        },
      });
    },
    error: function () {
      console.error("Error al obtener los datos del reporte de ingresos.");
    },
  });
})();

