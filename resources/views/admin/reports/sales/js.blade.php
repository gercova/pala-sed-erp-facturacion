<script>
let salesChart; 

document.addEventListener("DOMContentLoaded", function () {
    let table = $('#salesReportTable').DataTable({
        language: { url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
        dom: 'Bfrtip',
        buttons: ['copy', 'excel', 'pdf', 'print']
    });

    document.getElementById("formReport").addEventListener("submit", function (event) {
        event.preventDefault();
        
        let startDate = document.getElementById("start_date").value;
        let endDate = document.getElementById("end_date").value;

        fetch(`{{ route('report.sales.data') }}?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                const signo = data.signo || "$";
                table.clear();
                let sumTotal = 0, sumCant = 0, sumTicket = 0;

                data.sales.forEach(sale => {
                    sumTotal += Number(sale.total) || 0;
                    sumCant += parseInt(sale.cantidad_ventas);
                    
                    table.row.add([
                        sale.fecha,
                        sale.cantidad_ventas,
                        `${signo} ${parseFloat(sale.total - sale.total_impuestos).toFixed(2)}`,
                        `${signo} ${parseFloat(sale.total_impuestos).toFixed(2)}`,
                        `<strong>${signo} ${parseFloat(sale.total).toFixed(2)}</strong>`,
                        `${signo} ${parseFloat(sale.ticket_promedio).toFixed(2)}`
                    ]);
                });
                table.draw();

                document.getElementById('kpi-total-ventas').innerText = `${signo} ${sumTotal.toFixed(2)}`;
                document.getElementById('kpi-cantidad-ventas').innerText = sumCant;
                document.getElementById('kpi-ticket-promedio').innerText = `${signo} ${(sumTotal/sumCant || 0).toFixed(2)}`;
                renderChart(data.sales, signo);
            });
    });
});

function renderChart(salesData, signo) {
    const ctx = document.getElementById('salesChart').getContext('2d');
    
    if (salesChart) { salesChart.destroy(); }

    salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: salesData.map(s => s.fecha),
            datasets: [{
                label: 'Ventas Diarias',
                data: salesData.map(s => s.total),
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.05)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: (context) => `${signo} ${context.parsed.y.toFixed(2)}`
                    }
                }
            }
        }
    });
}

    </script>