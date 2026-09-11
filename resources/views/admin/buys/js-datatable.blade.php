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
                    last: "Ultimo",
                    next: "Siguiente",
                    previous: "Anterior"
                }
            },
            ajax: {
                url: "{{ route('buys.get') }}",
                data: function(d) {
                    d.filter_voucher = $('#voucher-filter').val();
                    d.filter_date = $('#date-filter').val();
                    d.filter_document = $('#document-filter').val();
                    d.filter_reason = $('#reason-filter').val();
                    d.filter_total = $('#total-filter').val();
                }
            },
            columns: [
                { data: 'documento', name: 'documento', className: 'text-center' },
                { data: 'fecha_emision', name: 'buys.fecha_emision', className: 'text-center' },
                { data: 'proveedor_info', name: 'clients.nombres', className: 'text-left' },
                { data: 'total', name: 'buys.total', className: 'text-center' },
                { data: 'estado_compra', name: 'estado_compra', orderable: false, searchable: false, className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass("pagination-sm");
            }
        });
    }

    $(document).ready(function() {
        const datatable = load_datatable();

        $('#voucher-filter, #date-filter, #document-filter, #reason-filter, #total-filter').on('keyup change', function() {
            datatable.ajax.reload();
        });
    });
</script>
