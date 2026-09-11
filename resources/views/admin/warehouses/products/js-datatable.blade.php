<script>
    function load_datatable() {
        const idwarehouse = $('.btn-add-product').val();

        const datatable = $('#table').DataTable({
            serverSide: true,
            paging: true,
            searching: true,
            destroy: true,
            responsive: false,
            ordering: false,
            autoWidth: false,
            bLengthChange: false,
            dom: '<"row"lr><"row"<"col-xs-12"t>><"row"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                decimal: '',
                emptyTable: 'No hay informaci&oacute;n',
                info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                infoFiltered: '(Filtrado de _MAX_ total)',
                lengthMenu: 'Mostrar _MENU_',
                loadingRecords: 'Cargando...',
                processing: 'Procesando...',
                zeroRecords: 'Sin resultados encontrados',
                paginate: {
                    first: 'Primero',
                    last: '&Uacute;ltimo',
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            },
            ajax: {
                url: "{{ route('admin.get_product_warehouse') }}",
                type: 'POST',
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.id = idwarehouse;
                    d.barcode = $('#barcode-filter-list').val();
                    d.code_intern = $('#code-intern-filter-list').val();
                    d.description = $('#description-filter-list').val();
                    d.price_buy = $('#price-buy-filter-list').val();
                    d.price_sale = $('#price-sale-filter-list').val();
                    d.stock_min = $('#stock-min-filter-list').val();
                    d.stock_act = $('#stock-act-filter-list').val();
                }
            },
            columns: [
                { name: 'products.codigo_barras', data: 'codigo_barras', className: 'text-center align-middle' },
                { name: 'products.codigo_interno', data: 'codigo_interno', className: 'text-center align-middle' },
                { name: 'products.descripcion', data: 'producto', className: 'align-middle' },
                { name: 'stock_products.precio_compra', data: 'precio_compra' },
                { name: 'stock_products.precio_venta', data: 'precio_venta' },
                { name: 'stock_products.stock_minimo', data: 'stock_minimo' },
                { name: 'stock_products.stock_actual', data: 'stock_actual', className: 'text-center align-middle' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center align-middle' },
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });

        $('#barcode-filter-list, #code-intern-filter-list, #description-filter-list, #price-buy-filter-list, #price-sale-filter-list, #stock-min-filter-list, #stock-act-filter-list')
            .off('keyup change')
            .on('keyup change', function() {
                $('#loading').show();
                $('#table').addClass('table-fade out');

                setTimeout(function() {
                    datatable.ajax.reload(function() {
                        $('#loading').hide();
                        $('#table').removeClass('out');
                    });
                }, 50);
            });
    }

    $(document).ready(function() {
        load_datatable();
    });
</script>
