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
                "loadingRecords": "Cargando...",
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
            "ajax": "{{ route('sale_notes.get') }}",
            "stripeClasses": [],
            "columns": [
            { data: 'documento', name: 'documento', className: 'text-center', searchable: true },
            { data: 'fecha_emision', name: 'sale_notes.fecha_emision', className: 'text-center' },
            { data: 'dni_ruc', name: 'clients.dni_ruc', className: 'text-center' },
            { data: 'cliente', name: 'clients.nombres', className: 'text-left' },
            { data: 'total', name: 'sale_notes.total', className: 'text-center' },
            { data: 'estado', orderable: false, searchable: false, className: 'text-center' },
            { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
        ],
            drawCallback: function () {
                $('.dataTables_paginate ul.pagination').addClass("pagination-sm");
            }
        });
        
        // Filtrar los datos
        
    }

    $(document).ready(function() { 
        let datatable = load_datatable(); // Almacena la instancia del DataTable
        $('#voucher-filter').on('keyup change', function () {
            datatable.column(0).search(this.value).draw(); // Usando el índice 0
        });

        $('#date-filter').on('change', function () {
            datatable.column(1).search(this.value).draw();
        });

        $('#document-filter').on('keyup change', function () {
            datatable.column(2).search(this.value).draw();
        });

        $('#reason-filter').on('keyup change', function () {
            datatable.column(3).search(this.value).draw();
        });

        $('#total-filter').on('keyup change', function () {
            datatable.column(4).search(this.value).draw();
        });
    });
</script>
