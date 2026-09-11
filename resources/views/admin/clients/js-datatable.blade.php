<script>
    function load_datatable() {
        return $('#table').DataTable({
            serverSide: true,
            paging: true,
            searching: false,
            destroy: true,
            responsive: true,
            ordering: false,
            autoWidth: false,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'Todos']
            ],
            dom: '<"row"lr><"row"<"col-xs-12"t>><"row"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                decimal: '',
                emptyTable: 'No hay informaciÃ³n',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(Filtrado de _MAX_ total)',
                thousands: ',',
                lengthMenu: 'Mostrar _MENU_',
                loadingRecords: 'Cargando datos...',
                processing: 'Procesando...',
                zeroRecords: 'Sin resultados encontrados',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            ajax: {
                url: "{{ route('clients.get') }}",
                data: function(d) {
                    d.filter_name = $('#name-filter').val();
                    d.filter_document = $('#dni-filter').val();
                }
            },
            columns: [
                { data: 'nombres', name: 'nombres', className: 'text-left' },
                { data: 'documento_info', name: 'nro_documento', className: 'text-center' },
                { data: 'acciones', name: 'acciones', searchable: false, className: 'text-center' },
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });
    }

    $(document).ready(function() {
        const datatable = load_datatable();

        $('#name-filter, #dni-filter').on('keyup change', function() {
            datatable.ajax.reload();
        });
    });
</script>
