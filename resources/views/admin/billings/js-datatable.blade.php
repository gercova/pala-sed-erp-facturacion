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
                [10, 25, 50, "Todos"]
            ],
            dom: '<"row"lr><"row"<"col-xs-12"t>><"row"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                decimal: "",
                emptyTable: "No hay información",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(Filtrado de _MAX_ total)",
                thousands: ",",
                lengthMenu: "Mostrar _MENU_",
                loadingRecords: "Cargando...",
                processing: "Procesando...",
                search: "",
                searchPlaceholder: "Buscar...",
                zeroRecords: "Sin resultados encontrados",
                paginate: {
                    first: "Primero",
                    last: "Último",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            },
            ajax: "{{ route('billings.get') }}",
            stripeClasses: [],
            columns: [
                { data: 'fecha_emision', name: 'billings.fecha_emision', className: 'text-center' },
                { data: 'comprobante', name: 'comprobante', className: 'text-center', searchable: true },
                { data: 'cliente_info', name: 'clients.nombres' },
                { data: 'almacen_badge', name: 'warehouses.descripcion', className: 'text-center', orderable: false, searchable: false },
                { data: 'total', name: 'billings.total', className: 'text-center' },
                { data: 'xml', orderable: false, searchable: false, className: 'text-center' },
                { data: 'cdr_archivo', orderable: false, searchable: false, className: 'text-center' },
                { data: 'sunat_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'estado_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass("pagination-sm");
            }
        });
    }

    $(document).ready(function() {
        let datatable = load_datatable();

        $('#date-filter').on('change', function() {
            datatable.column(0).search(this.value).draw();
        });

        $('#voucher-filter').on('keyup change', function() {
            datatable.column(1).search(this.value).draw();
        });

        $('#reason-filter').on('keyup change', function() {
            datatable.column(2).search(this.value).draw();
        });

        $('#total-filter').on('keyup change', function() {
            datatable.column(4).search(this.value).draw();
        });
    });
</script>
