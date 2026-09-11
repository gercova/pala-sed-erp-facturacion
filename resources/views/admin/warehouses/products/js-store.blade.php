<script>
    let warehouseDetailDatatable = null;

    function notifyWarehouseAjaxError(xhr) {
        const message = xhr?.responseJSON?.msg || 'Hubo un error en la solicitud';
        toast_msg(message, 'warning');
    }

    function isNonNegativeNumber(value) {
        if (value === null || value === undefined) {
            return false;
        }

        const normalized = String(value).trim();
        if (normalized === '') {
            return false;
        }

        return !isNaN(normalized) && Number(normalized) >= 0;
    }

    $('body').on('click', '.btn-add-product', function(event) {
        event.preventDefault();
        const id = $(this).val();

        $.ajax({
            url: "{{ route('admin.list_products_warehouse') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                id: id
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');
                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                load_datatable_detail(r.id);
                $('#modalDetailProducts').modal('show');
            },
            dataType: 'json'
        });
    });

    function load_datatable_detail(id) {
        warehouseDetailDatatable = $('#table_detail').DataTable({
            serverSide: true,
            paging: true,
            searching: true,
            destroy: true,
            responsive: false,
            ordering: false,
            autoWidth: false,
            bLengthChange: false,
            lengthMenu: [
                [10, 25, 50, -1],
                [10, 25, 50, 'Todos']
            ],
            dom: '<"row"lr><"row"<"col-xs-12"t>><"row"<"col-sm-6"i><"col-sm-6"p>>',
            language: {
                decimal: '',
                emptyTable: 'No hay informaci&oacute;n',
                info: 'Mostrando _START_ a _END_ de _TOTAL_',
                infoEmpty: 'Mostrando 0 a 0 de 0',
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
                url: "{{ route('admin.get_detail_products') }}",
                data: function(d) {
                    d._token = "{{ csrf_token() }}";
                    d.id = id;
                    d.search_barcode = $('#barcode-filter').val();
                    d.search_code_intern = $('#code-intern-filter').val();
                    d.search_description = $('#description-filter').val();
                    d.search_category = $('#category-filter').val();
                },
                type: 'POST'
            },
            columns: [
                { data: 'checkbox', name: 'checkbox', className: 'text-center' },
                { data: 'codigo_barras', name: 'products.codigo_barras', className: 'text-center' },
                { data: 'codigo_interno', name: 'products.codigo_interno', className: 'text-center' },
                { data: 'descripcion', name: 'products.descripcion', className: 'text-left' },
                { data: 'categoria', name: 'categories.descripcion', className: 'text-center' },
                { data: 'stock_inicial', name: 'stock_inicial', className: 'text-center' },
            ],
            drawCallback: function() {
                $('.dataTables_paginate ul.pagination').addClass('pagination-sm');
            }
        });

        $('#barcode-filter, #code-intern-filter, #description-filter, #category-filter')
            .off('keyup change')
            .on('keyup change', function() {
                warehouseDetailDatatable.ajax.reload();
            });
    }

    $('body').on('click', '.btn-checkbox', function() {
        const $stockInput = $(this).closest('tr').find('td:eq(5) input');

        if ($(this).is(':checked')) {
            $stockInput.prop('disabled', false).focus();
            return;
        }

        $stockInput.prop('disabled', true).val('').removeClass('is-invalid');
    });

    $('body').on('click', '.btn-save-detail', function(event) {
        event.preventDefault();

        const idalmacen = $(this).data('idalmacen');
        const productos = [];
        let hasValidationError = false;

        $('#table_detail tbody').find('input:checkbox:checked').each(function() {
            const $row = $(this).closest('tr');
            const $stockInput = $row.find('td:eq(5) input');
            const tipoProducto = $stockInput.data('tipo');
            const stockInicial = $stockInput.val();
            const idproducto = $stockInput.data('id');
            const precioCompra = $stockInput.data('precio_compra');
            const precioVenta = $stockInput.data('precio_venta');

            if (tipoProducto === 'producto' && stockInicial.trim() === '') {
                $stockInput.addClass('is-invalid');
                hasValidationError = true;
                return;
            }

            $stockInput.removeClass('is-invalid');
            productos.push({
                stock_inicial: tipoProducto === 'producto' ? parseInt(stockInicial, 10) : null,
                idproducto: parseInt(idproducto, 10),
                precio_compra: parseFloat(precioCompra),
                precio_venta: parseFloat(precioVenta)
            });
        });

        if (hasValidationError) {
            toast_msg('Ingrese el stock inicial de los productos seleccionados', 'warning');
            return;
        }

        if (productos.length === 0) {
            toast_msg('No hay productos seleccionados para agregar al almac&eacute;n', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.save_product_stock') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                productos: JSON.stringify(productos),
                idalmacen: idalmacen
            },
            beforeSend: function() {
                $('.btn-save-detail').prop('disabled', true);
                $('.text-saving-detail').removeClass('d-none');
                $('.text-save-detail').addClass('d-none');
            },
            success: function(r) {
                $('.btn-save-detail').prop('disabled', false);
                $('.text-saving-detail').addClass('d-none');
                $('.text-save-detail').removeClass('d-none');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#modalDetailProducts').modal('hide');
                toast_msg(r.msg, r.type);
                reload_table();
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-add-for-barcode', function(event) {
        event.preventDefault();
        $('#modalAddBarcodeStock').modal('show');
        setTimeout(() => {
            $('#form_barcode_stock input[name="barcode"]').focus();
        }, 300);
    });

    $('#form_barcode_stock').on('submit', function(event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('admin.barcode_sum_product') }}",
            method: 'POST',
            data: $(this).serialize(),
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');
                toast_msg(r.msg, r.type);

                $('#form_barcode_stock input[name="barcode"]').val('');

                if (r.status) {
                    reload_table();
                }
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-all-products', function(event) {
        event.preventDefault();

        if ($('#table_detail tbody tr').length === 0) {
            toast_msg('No tiene productos para agregar', 'warning');
            return;
        }

        $('#modalDetailProducts').modal('hide');
        $('#modalStockInitial').modal('show');
    });

    $('body').on('click', '#btnCloseStockInitial', function() {
        $('#modalStockInitial').modal('hide');
        $('#modalDetailProducts').modal('show');
    });

    $('body').on('click', '.btn-stock-initial', function(event) {
        event.preventDefault();
        const stockInicial = $('input[name="value_stock_inicial"]').val();

        if (!isNonNegativeNumber(stockInicial)) {
            toast_msg('Ingrese un stock inicial v&aacute;lido mayor o igual a cero', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.save_products_stock_all') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                idalmacen: $(this).data('idalmacen'),
                stock_inicial: stockInicial
            },
            beforeSend: function() {
                $('.btn-stock-initial').prop('disabled', true);
                $('.text-storing-initial').removeClass('d-none');
                $('.text-stock-initial').addClass('d-none');
            },
            success: function(r) {
                $('.btn-stock-initial').prop('disabled', false);
                $('.text-storing-initial').addClass('d-none');
                $('.text-stock-initial').removeClass('d-none');
                toast_msg(r.msg, r.type);

                if (!r.status) {
                    return;
                }

                $('#modalStockInitial input[name="value_stock_inicial"]').val('');
                $('#modalStockInitial').modal('hide');
                $('#modalDetailProducts').modal('hide');
                reload_table();
            },
            error: function(xhr) {
                $('.btn-stock-initial').prop('disabled', false);
                $('.text-storing-initial').addClass('d-none');
                $('.text-stock-initial').removeClass('d-none');
                notifyWarehouseAjaxError(xhr);
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-detail-sum', function(event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('admin.detail_sum_product') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                idalmacen: $(this).data('idalmacen'),
                idproducto: $(this).data('idproducto')
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');
                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }
                $('#form_sum_stock input[name="idalmacen"]').val(r.stock.idalmacen);
                $('#form_sum_stock input[name="idproducto"]').val(r.stock.idproducto);
                $('#modalSumStock').modal('show');
            },
            dataType: 'json'
        });
    });

    touch_down('#form_sum_stock input[name="cantidad"]', 'product');
    touch_up('#form_sum_stock input[name="cantidad"]', 'product');

    $('body').on('click', '.btn-sum-stock', function(event) {
        event.preventDefault();
        const cantidad = $('#form_sum_stock input[name="cantidad"]');

        cantidad.toggleClass('is-invalid', cantidad.val().trim() === '');

        if (!isNonNegativeNumber(cantidad.val()) || Number(cantidad.val()) <= 0) {
            toast_msg('Ingrese una cantidad v&aacute;lida mayor a cero', 'warning');
            return;
        }

        if (cantidad.val().trim() === '') {
            return;
        }

        $.ajax({
            url: "{{ route('admin.sum_stock_product') }}",
            method: 'POST',
            data: $('#form_sum_stock').serialize(),
            beforeSend: function() {
                $('.btn-sum-stock').prop('disabled', true);
                $('.text-summing-stock').removeClass('d-none');
                $('.text-sum-stock').addClass('d-none');
            },
            success: function(r) {
                $('.btn-sum-stock').prop('disabled', false);
                $('.text-summing-stock').addClass('d-none');
                $('.text-sum-stock').removeClass('d-none');
                toast_msg(r.msg, r.type);

                if (!r.status) {
                    return;
                }

                $('#form_sum_stock input[name="cantidad"]').val('1');
                $('#modalSumStock').modal('hide');
                reload_table();
            },
            error: function(xhr) {
                $('.btn-sum-stock').prop('disabled', false);
                $('.text-summing-stock').addClass('d-none');
                $('.text-sum-stock').removeClass('d-none');
                notifyWarehouseAjaxError(xhr);
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-detail-stock', function(event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('admin.detail_stock_product') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                idalmacen: $(this).data('idalmacen'),
                idproducto: $(this).data('idproducto')
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');
                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }
                $('#form_edit_stock input[name="idalmacen"]').val(r.stock.idalmacen);
                $('#form_edit_stock input[name="idproducto"]').val(r.stock.idproducto);
                $('#form_edit_stock input[name="stock_actual"]').val(r.stock.stock_actual);
                $('#modalUpdateStock').modal('show');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-store-stock', function(event) {
        event.preventDefault();
        const stockActual = $('#form_edit_stock input[name="stock_actual"]');

        stockActual.toggleClass('is-invalid', stockActual.val().trim() === '');

        if (!isNonNegativeNumber(stockActual.val())) {
            toast_msg('Ingrese un stock v&aacute;lido mayor o igual a cero', 'warning');
            return;
        }

        if (stockActual.val().trim() === '') {
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_stock_product') }}",
            method: 'POST',
            data: $('#form_edit_stock').serialize(),
            beforeSend: function() {
                $('.btn-store-stock').prop('disabled', true);
                $('.text-storing-stock').removeClass('d-none');
                $('.text-store-stock').addClass('d-none');
            },
            success: function(r) {
                $('.btn-store-stock').prop('disabled', false);
                $('.text-storing-stock').addClass('d-none');
                $('.text-store-stock').removeClass('d-none');
                toast_msg(r.msg, r.type);

                if (!r.status) {
                    return;
                }

                $('#modalUpdateStock').modal('hide');
                reload_table();
            },
            error: function(xhr) {
                $('.btn-store-stock').prop('disabled', false);
                $('.text-storing-stock').addClass('d-none');
                $('.text-store-stock').removeClass('d-none');
                notifyWarehouseAjaxError(xhr);
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-confirm-stock', function(event) {
        event.preventDefault();
        const idalmacen = $(this).data('idalmacen');
        const idproducto = $(this).data('idproducto');

        Swal.fire({
            title: '&iquest;Desea eliminar el registro?',
            text: 'Esta acci&oacute;n no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'S&iacute;, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: "{{ route('admin.delete_stock_product') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    idalmacen: idalmacen,
                    idproducto: idproducto
                },
                beforeSend: function() {
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Por favor, espere',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                },
                success: function(r) {
                    Swal.close();
                    toast_msg(r.msg, r.type);
                    if (r.status) {
                        reload_table();
                    }
                },
                error: function() {
                    Swal.close();
                    toastr.error('Hubo un error en la solicitud', 'Error');
                }
            });
        });
    });

    function persistInlineWarehouseField(payload) {
        $.ajax({
            url: "{{ route('admin.store_product_stocks') }}",
            method: 'POST',
            data: Object.assign({ _token: "{{ csrf_token() }}" }, payload),
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');
                toast_msg(r.msg, r.type);
                if (r.status) {
                    reload_table();
                }
            },
            error: function(xhr) {
                close_block('#layout-content');
                notifyWarehouseAjaxError(xhr);
            },
            dataType: 'json'
        });
    }

    $('body').on('keyup', '.input-stock-minimo', function(e) {
        if ((e.keyCode ? e.keyCode : e.which) !== 13) {
            return;
        }

        let value = $(this).val();
        if (!isNonNegativeNumber(value)) {
            $(this).val(0);
            toast_msg('Ingrese un n&uacute;mero v&aacute;lido mayor o igual a cero', 'warning');
            return;
        }

        if (value.trim() === '') {
            value = 0;
            $(this).val(0);
        }

        persistInlineWarehouseField({
            idalmacen: $(this).data('idalmacen'),
            idproducto: $(this).data('idproducto'),
            stock_minimo: value,
            precio_compra: $(this).data('precio_compra'),
            precio_venta: $(this).data('precio_venta')
        });
    });

    $('body').on('keyup', '.input-precio-compra', function(e) {
        if ((e.keyCode ? e.keyCode : e.which) !== 13) {
            return;
        }

        let value = $(this).val();
        if (!isNonNegativeNumber(value)) {
            $(this).val(0);
            toast_msg('Ingrese un n&uacute;mero v&aacute;lido mayor o igual a cero', 'warning');
            return;
        }

        if (value.trim() === '') {
            value = 0;
            $(this).val(0);
        }

        persistInlineWarehouseField({
            idalmacen: $(this).data('idalmacen'),
            idproducto: $(this).data('idproducto'),
            precio_compra: value,
            precio_venta: $(this).data('precio_venta'),
            stock_minimo: $(this).data('stock_minimo')
        });
    });

    $('body').on('keyup', '.input-precio-venta', function(e) {
        if ((e.keyCode ? e.keyCode : e.which) !== 13) {
            return;
        }

        let value = $(this).val();
        if (!isNonNegativeNumber(value)) {
            $(this).val(0);
            toast_msg('Ingrese un n&uacute;mero v&aacute;lido mayor o igual a cero', 'warning');
            return;
        }

        if (value.trim() === '') {
            value = 0;
            $(this).val(0);
        }

        persistInlineWarehouseField({
            idalmacen: $(this).data('idalmacen'),
            idproducto: $(this).data('idproducto'),
            precio_venta: value,
            precio_compra: $(this).data('precio_compra'),
            stock_minimo: $(this).data('stock_minimo')
        });
    });

    $('body').on('click', '.btn-download-excel', function(event) {
        event.preventDefault();
        $('#excelModal').modal('show');
    });

    $('body').on('click', '.btn-upload-excel-products', function(event) {
        event.preventDefault();
        const form = new FormData($('#uploadExcelForm')[0]);

        $.ajax({
            url: "{{ route('admin.upload_excel_warehouse') }}",
            method: 'POST',
            data: form,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.btn-upload-excel-products').prop('disabled', true);
                $('.text-upload-product').addClass('d-none');
                $('.text-uploads-product').removeClass('d-none');
            },
            success: function(r) {
                $('.btn-upload-excel-products').prop('disabled', false);
                $('.text-upload-product').removeClass('d-none');
                $('.text-uploads-product').addClass('d-none');
                toast_msg(r.msg, r.type);
                if (!r.status) {
                    return;
                }

                $('#uploadExcelForm').trigger('reset');
                $('#excelModal').modal('hide');
                reload_table();
            },
            error: function(xhr) {
                $('.btn-upload-excel-products').prop('disabled', false);
                $('.text-upload-product').removeClass('d-none');
                $('.text-uploads-product').addClass('d-none');
                notifyWarehouseAjaxError(xhr);
            },
            dataType: 'json'
        });
    });
</script>
