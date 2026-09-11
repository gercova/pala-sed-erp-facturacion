<script>
    $(function () {
        const table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('shipment_guides.get') }}",
                data: function (d) {
                    d.filter_date = $('#filter_date').val();
                    d.filter_document = $('#filter_document').val();
                    d.filter_customer = $('#filter_customer').val();
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'fecha_emision', name: 'shipment_guides.fecha_emision' },
                { data: 'guia', name: 'guia', orderable: false, searchable: false },
                { data: 'cliente_info', name: 'clients.nombres', orderable: false, searchable: false },
                { data: 'modo_badge', name: 'shipment_guides.modo_transporte', orderable: false, searchable: false },
                { data: 'almacen_badge', name: 'warehouses.descripcion', orderable: false, searchable: false },
                { data: 'xml', name: 'xml', orderable: false, searchable: false, className: 'text-center' },
                { data: 'cdr_archivo', name: 'cdr', orderable: false, searchable: false, className: 'text-center' },
                { data: 'gre_badge', name: 'estado_cpe', orderable: false, searchable: false, className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: {
                processing: "Procesando...",
                search: "Buscar:",
                lengthMenu: "Mostrar _MENU_",
                info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                infoEmpty: "Mostrando 0 a 0 de 0 registros",
                infoFiltered: "(filtrado de _MAX_ registros)",
                loadingRecords: "Cargando...",
                zeroRecords: "No se encontraron registros",
                emptyTable: "No hay datos disponibles",
                paginate: {
                    first: "Primero",
                    previous: "Anterior",
                    next: "Siguiente",
                    last: "Último"
                }
            }
        });

        $('#filter_date, #filter_document, #filter_customer').on('change keyup', function () {
            table.ajax.reload();
        });
    });
</script>
