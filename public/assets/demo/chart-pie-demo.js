// Chart.js v4 (dashboard)
(function () {
  const el = document.getElementById("myPieChart");
  if (!el || typeof Chart === "undefined") return;

  const COLORS = [
    "rgba(0, 97, 242, 0.95)",
    "rgba(0, 172, 105, 0.95)",
    "rgba(88, 0, 232, 0.95)",
    "rgba(232, 136, 0, 0.95)",
    "rgba(232, 21, 0, 0.95)",
  ];
  const fmt = (signo, v) => `${signo}${Number(v || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

  $.ajax({
    url: urlMetodosPagoVentas,
    type: "GET",
    dataType: "json",
    success: function (response) {
      const signo = response.signo || "S/ ";
      const labels = response.labels || [];
      const values = (response.data || []).map((v) => Number(v) || 0);

      new Chart(el.getContext("2d"), {
        type: "doughnut",
        data: {
          labels,
          datasets: [
            {
              data: values,
              backgroundColor: labels.map((_, i) => COLORS[i % COLORS.length]),
              borderColor: "rgba(255,255,255,0.9)",
              borderWidth: 2,
              hoverOffset: 6,
            },
          ],
        },
        options: {
          maintainAspectRatio: false,
          plugins: {
            legend: { display: false },
            tooltip: {
              callbacks: {
                label: (ctx) => `${ctx.label}: ${fmt(signo, ctx.parsed)}`,
              },
            },
          },
          cutout: "72%",
        },
      });

      const listaPagos = $(".listaMetodosPago");
      listaPagos.empty();
      labels.forEach((label, index) => {
        const htmlItem = `
          <div class="list-group-item d-flex align-items-center justify-content-between small px-0 py-2">
              <div class="me-3 d-flex align-items-center">
                  <span class="me-2" style="width:10px;height:10px;border-radius:999px;background:${COLORS[index % COLORS.length]};display:inline-block"></span>
                  ${label}
              </div>
              <div class="fw-500 text-dark">${fmt(signo, values[index])}</div>
          </div>
        `;
        listaPagos.append(htmlItem);
      });
    },
    error: function () {
      console.error("Error al obtener los datos de métodos de pago.");
    },
  });
})();

