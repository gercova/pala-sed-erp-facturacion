<script>
    let archingCashTable;
    let archingMovementsTable;

    function load_datatable() {
        archingCashTable = $('#table').DataTable({
            serverSide: true,
            paging: true,
            searching: true,
            destroy: true,
            responsive: false,
            ordering: false,
            autoWidth: false,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'Todos']
            ],
            dom: '<"row"lr><"row"<"col-xs-12"t>><"row"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                decimal: '',
                emptyTable: 'No hay informacion',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(Filtrado de _MAX_ total)',
                thousands: ',',
                lengthMenu: 'Mostrar _MENU_',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: '',
                searchPlaceholder: 'Buscar...',
                zeroRecords: 'Sin resultados encontrados',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            ajax: {
                url: "{{ route('arching_cashes.get') }}",
                data: function (d) {
                    d.filter_date = $('#date-filter').val();
                    d.filter_responsible = $('#responsible-filter').val();
                    d.filter_cash = $('#cash-filter').val();
                    d.filter_status = $('#status-filter').val();
                }
            },
            stripeClasses: [],
            columns: [
                { data: 'fecha_inicio', name: 'arching_cashes.fecha_inicio', className: 'text-center' },
                { data: 'responsable', name: 'users.nombres', className: 'text-center' },
                { data: 'caja_info', name: 'cashes.descripcion', className: 'text-center' },
                { data: 'monto_apertura', name: 'arching_cashes.monto_inicial', className: 'text-center' },
                { data: 'monto_cierre', name: 'arching_cashes.monto_final', className: 'text-center' },
                { data: 'estado_badge', name: 'arching_cashes.estado', orderable: false, searchable: false, className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            drawCallback: function () {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });

        return archingCashTable;
    }

    function load_arching_movements_datatable(id) {
        archingMovementsTable = $('#archingMovementsTable').DataTable({
            serverSide: true,
            paging: true,
            searching: true,
            destroy: true,
            responsive: false,
            ordering: false,
            autoWidth: false,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'Todos']
            ],
            dom: '<"row"lr><"row"<"col-xs-12"t>><"row"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                decimal: '',
                emptyTable: 'No hay movimientos registrados',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(Filtrado de _MAX_ total)',
                thousands: ',',
                lengthMenu: 'Mostrar _MENU_',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                search: '',
                searchPlaceholder: 'Buscar...',
                zeroRecords: 'Sin resultados encontrados',
                paginate: {
                    first: 'Primero',
                    last: 'Ultimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            ajax: {
                url: "{{ route('admin.get_detail_cashes') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                }
            },
            stripeClasses: [],
            columns: [
                { data: 'fecha', name: 'fecha', className: 'text-center' },
                { data: 'hora', name: 'hora', className: 'text-center' },
                { data: 'documento_info', name: 'documento', className: 'text-center' },
                { data: 'cliente_info', name: 'cliente', className: 'text-start' },
                { data: 'pago', name: 'pago', className: 'text-center' },
                { data: 'total', name: 'total', className: 'text-end' }
            ],
            drawCallback: function () {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });

        return archingMovementsTable;
    }

    $(document).ready(function () {
        load_datatable();

        $('#date-filter, #cash-filter, #status-filter').on('change', function () {
            archingCashTable.ajax.reload();
        });

        $('#responsible-filter').on('keyup change', function () {
            archingCashTable.ajax.reload();
        });
    });
</script>
