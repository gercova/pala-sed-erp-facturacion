<script>
document.addEventListener("DOMContentLoaded", function () {
    document.getElementById("formReport").addEventListener("submit", function (event) {
        event.preventDefault();

        let startDate = document.getElementById("start_date").value;
        let endDate = document.getElementById("end_date").value;

        if (!startDate || !endDate) {
            toast_msg("Seleccione un rango de fechas válido.", "warning");
            return;
        }

        fetch(`{{ route('report.sales.products') }}?start_date=${startDate}&end_date=${endDate}`)
            .then(response => response.json())
            .then(data => {
                let tableBody = document.querySelector("#salesReportTable tbody");
                tableBody.innerHTML = "";

                if (!data.sales || data.sales.length === 0) {
                    tableBody.innerHTML = "<tr><td colspan='4' class='text-center'>No hay datos disponibles</td></tr>";
                    return;
                }

                data.sales.forEach(sale => {
                    let row = `<tr>
                        <td class="text-center">${sale.producto}</td> 
                        <td class="text-center">${parseInt(sale.cantidad_vendida)}</td>
                        <td class="text-center">${data.signo} ${parseFloat(sale.total_ventas).toFixed(2)}</td>
                        <td class="text-center">${data.signo} ${parseFloat(sale.precio_promedio).toFixed(2)}</td>
                    </tr>`;
                    tableBody.innerHTML += row;
                });
            })
            .catch(error => console.error("Error al obtener los datos:", error));
    });
});

    </script>
    