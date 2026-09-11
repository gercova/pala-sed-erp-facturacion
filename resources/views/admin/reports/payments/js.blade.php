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
    
            fetch(`{{ route('report.sales.payment_methods') }}?start_date=${startDate}&end_date=${endDate}`)
                .then(response => response.json())
                .then(data => {
                    let tableBody = document.querySelector("#salesReportTable tbody");
                    tableBody.innerHTML = "";
    
                    if (data.sales.length === 0) {
                        tableBody.innerHTML = "<tr><td colspan='3' class='text-center'>No hay datos disponibles</td></tr>";
                        return;
                    }
    
                    data.sales.forEach(sale => {
                        let row = `<tr>
                            <td class="text-center">${sale.metodo_pago}</td>
                            <td class="text-center">${sale.cantidad_transacciones}</td>
                            <td class="text-center">${data.signo} ${parseFloat(sale.total_recaudado).toFixed(2)}</td>
                        </tr>`;
                        tableBody.innerHTML += row;
                    });
                })
                .catch(error => console.error("Error al obtener los datos:", error));
        });
    });
    </script>
    