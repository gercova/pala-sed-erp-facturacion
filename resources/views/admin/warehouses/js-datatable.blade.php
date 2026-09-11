<script>
    function load_datatable() {
        let datatable = $('#table').DataTable({
            serverSide: true,
            "paging": true,
            "searching": true,
            "destroy": true,
            responsive: true,
            ordering: false,
            autoWidth: false,
            "lengthMenu": [
                [10, 25, 50, -1],
                [10, 25, 50, "Todos"]
            ],
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
            "ajax": "{{ route('warehouses.get') }}",
            "stripeClasses": [],
            "columns": [
                { data: 'pin_column', name: 'pin_column', orderable: false, searchable: false, className: 'text-center' },
                { data: 'descripcion_completa', name: 'descripcion_completa', className: 'text-left' },
                { data: 'unidades', name: 'unidades', className: 'text-center align-middle' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center align-middle' }
            ],
            drawCallback: function () {
                $('.dataTables_paginate ul.pagination').addClass("pagination-sm");
            }
        });
    }

    $(document).ready(function() { load_datatable(); });
</script>
