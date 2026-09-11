<script>
    function load_datatable() {
        $('#table').DataTable({
            serverSide: true,
            paging: true,
            searching: true,
            destroy: true,
            responsive: true,
            ordering: false,
            autoWidth: false,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'Todos']
            ],
            language: {
                decimal: '',
                emptyTable: 'No hay información',
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
            ajax: "{{ route('users.get') }}",
            stripeClasses: [],
            columns: [
                { data: 'usuario_info', name: 'users.nombres', className: 'text-start' },
                { data: 'caja', name: 'cashes.descripcion', className: 'text-center' },
                { data: 'rol', name: 'roles.name', className: 'text-center', orderable: false, searchable: false },
                { data: 'almacenes', name: 'warehouses.descripcion', className: 'text-center', orderable: false, searchable: false },
                { data: 'estado_badge', name: 'users.estado', className: 'text-center', orderable: false, searchable: false },
                { data: 'acciones', name: 'acciones', className: 'text-center', orderable: false, searchable: false }
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });
    }

    $(document).ready(function() {
        load_datatable();
    });
</script>
