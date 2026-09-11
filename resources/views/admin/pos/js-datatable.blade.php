<script>
    function load_datatable() {
        return $('#table').DataTable({
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
            ajax: "{{ route('admin.pos.get') }}",
            stripeClasses: [],
            columns: [
                { data: 'issue_date', name: 'issue_date', className: 'text-center' },
                { data: 'document_type_badge', name: 'document_type', className: 'text-center', orderable: false, searchable: false },
                { data: 'document_info', name: 'document_number', className: 'text-center', orderable: false, searchable: false },
                { data: 'customer_info', name: 'customer_name', className: 'text-left', orderable: false, searchable: false },
                { data: 'total_badge', name: 'total', className: 'text-center', orderable: false, searchable: false },
                { data: 'status_badge', name: 'status_label', className: 'text-center', orderable: false, searchable: false }
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });
    }

    $(document).ready(function() {
        const datatable = load_datatable();

        $('#date-filter').on('change', function() {
            datatable.column(0).search(this.value).draw();
        });

        $('#type-filter').on('keyup change', function() {
            datatable.column(1).search(this.value).draw();
        });

        $('#document-filter').on('keyup change', function() {
            datatable.column(2).search(this.value).draw();
        });

        $('#customer-filter').on('keyup change', function() {
            datatable.column(3).search(this.value).draw();
        });

        $('#total-filter').on('keyup change', function() {
            datatable.column(4).search(this.value).draw();
        });

        $('#status-filter').on('keyup change', function() {
            datatable.column(5).search(this.value).draw();
        });
    });
</script>
