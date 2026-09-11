<script>
    function load_datatable() {
        return $('#table').DataTable({
            serverSide: true,
            "paging": true,
            "searching": true,
            "destroy": true,
            responsive: false,
            ordering: false,
            autoWidth: false,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "Todos"]
            ],
            "dom": '<"row"lr><"row"<"col-xs-12"t>><"row"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                "decimal": "",
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
                "infoEmpty": "Mostrando 0 a 0 de 0 registros",
                "infoFiltered": "(Filtrado de _MAX_ total)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_",
                "loadingRecords": "Cargando datos...",
                "processing": "Procesando...",
                "search": "",
                "searchPlaceholder": "Buscar...",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "ajax": "{{ route('admin.get_transfer_orders') }}",
            "columns": [
                { data: 'correlativo', name: 'transfer_orders.correlativo', className: 'text-center' },
                { data: 'fecha_emision', name: 'transfer_orders.fecha_emision', className: 'text-center' },
                { data: 'almacen_despacho', name: 'despacho.descripcion', className: 'text-center' },
                { data: 'almacen_receptor', name: 'receptor.descripcion', className: 'text-center' },
                { data: 'estado_compra', orderable: false, searchable: false, className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass("pagination-sm");
            }
        });
    }

    $(document).ready(function() {
        let datatable = load_datatable(); // Almacena la instancia del DataTable

        // Filtros para documento
        $('#document-filter').on('keyup change', function() {
            datatable.column(0).search(this.value).draw(); // Índice correcto
        });

        // Filtros para fecha
        $('#date-filter').on('change', function() {
            datatable.column(1).search(this.value).draw(); // Índice correcto
        });

        // Filtros para almacén despacho
        $('#dispatch-filter').on('keyup change', function() {
            datatable.column(2).search(this.value).draw(); // Índice correcto
        });

        // Filtros para almacén receptor
        $('#receipt-filter').on('keyup change', function() {
            datatable.column(3).search(this.value).draw(); // Índice correcto
        });
    });
</script>
