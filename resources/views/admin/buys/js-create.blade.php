<script>
    function success_save_provider(msg = null, type = null, last_id = null)
    {
        load_providers(last_id);
    }

    function initializeBuySelects()
    {
        $('#form_save_buy select[name="idtipo_comprobante"]').select2({
            placeholder: "[SELECCIONE]"
        });

        $('#form_save_buy select[name="modo_pago"]').select2({
            placeholder: "[SELECCIONE]"
        });

        $('#form_save_buy select[name="dni_ruc"]').select2({
            placeholder: "[SELECCIONE]"
        });
    }

    function isValidPositiveNumber(value)
    {
        return value !== null && value !== '' && !isNaN(value) && Number(value) > 0;
    }

    function toggleBuySaveButton(loading)
    {
        $('#form_save_buy .btn-save').prop('disabled', loading);
        $('#form_save_buy .text-save').toggleClass('d-none', loading);
        $('#form_save_buy .text-saving').toggleClass('d-none', !loading);
    }

    function toggleProductSaveButton(loading)
    {
        $('#form_save_to_product .btn-save').prop('disabled', loading);
        $('#form_save_to_product .text-save').toggleClass('d-none', loading);
        $('#form_save_to_product .text-saving').toggleClass('d-none', !loading);
    }

    function resetProductModal()
    {
        $('#form_save_to_product select[name="idalmacen"]').val('').trigger('change');
        $('#form_save_to_product select[name="product"]').html('<option value=""></option>').val('').trigger('change');
        $('#form_save_to_product input[name="cantidad"]').val('1');
        $('#form_save_to_product input[name="precio"]').val('');
        $('#modalAddToProduct #wrapper-product').addClass('d-none');
    }

    function load_cart()
    {
        $.ajax({
            url: "{{ route('admin.load_cart_buys') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                $('#tbody_buys').html(r.html_cart);
                $('#wrapper_totals').html(r.html_totales);
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo cargar el detalle de la compra.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    }

    function load_providers(selectedId = null)
    {
        $.ajax({
            url: "{{ route('admin.load_providers') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}"
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                let htmlProviders = '<option value=""></option>';
                $.each(r.providers, function(index, provider) {
                    htmlProviders += `<option value="${provider.id}">${provider.nro_documento + ' - ' + provider.nombres}</option>`;
                });

                const $providerSelect = $('#form_save_buy select[name="dni_ruc"]');
                $providerSelect.html(htmlProviders);
                if (selectedId !== null && selectedId !== undefined && selectedId !== '') {
                    $providerSelect.val(String(selectedId));
                }
                $providerSelect.trigger('change');
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo recargar la lista de proveedores.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    }

    function existenRequeridosVacios()
    {
        let requeridosVacios = false;
        let ids = [];

        $('.reque').each(function() {
            const fieldId = $(this).attr('id');
            if ($(this).val() === "") {
                $(`#form_save_buy #${fieldId}`).addClass('is-invalid');
                requeridosVacios = true;
                ids.push(fieldId);
            } else {
                $(`#form_save_buy #${fieldId}`).removeClass('is-invalid');
            }
        });

        return {
            status: requeridosVacios,
            ids: ids
        };
    }

    $('body').on('change', '#form_save_to_product select[name="idalmacen"]', function() {
        const value = $(this).val();
        if (!value || value.trim() === '') {
            $('#modalAddToProduct #wrapper-product').addClass('d-none');
            $('#form_save_to_product select[name="product"]').html('<option value=""></option>').trigger('change');
            return;
        }

        $.ajax({
            url: "{{ route('admin.get_products_by_idwarehouse_b') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                idalmacen: value
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                close_block('#layout-content');
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                let html = '<option value=""></option>';
                $.each(r.productos, function(index, producto) {
                    html += `<option value="${producto.idproducto}" data-idalmacen="${producto.idalmacen}">${producto.producto}</option>`;
                });

                $('#modalAddToProduct #wrapper-product').removeClass('d-none');
                $('#form_save_to_product select[name="product"]').html(html).trigger('change');
                $('#form_save_to_product select[name="product"]').select2('open');
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudieron cargar los productos del almacen.', xhr.responseJSON?.type || 'error');
            },
            dataType: "json"
        });
    });

    $('#modalAddToProduct select[name="product"]').on('change', function() {
        const value = $(this).val();
        const idalmacen = $(this).find(':selected').data('idalmacen');

        $('#form_save_to_product input[name="precio"]').val('');

        if (!value || String(value).trim() === '' || !idalmacen) {
            return;
        }

        $.ajax({
            url: "{{ route('admin.get_product_buy_purchase') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: value,
                idalmacen: idalmacen
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                $('#form_save_to_product input[name="precio"]').val(r.product.precio_compra);
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo obtener el precio del producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: "json"
        });
    });

    touch_down('#form_save_to_product input[name="cantidad"]', 'product');
    touch_up('#form_save_to_product input[name="cantidad"]', 'product');

    $('body').on('click', '.btn-add-product', function(event) {
        event.preventDefault();
        resetProductModal();
        $('#modalAddToProduct').modal('show');
    });

    $('body').on('click', '#form_save_to_product .btn-save', function(event) {
        event.preventDefault();

        const selectProduct = $('#form_save_to_product select[name="product"]').val();
        const idalmacen = $('#form_save_to_product select[name="idalmacen"]').val();
        const cantidad = parseFloat($('#form_save_to_product input[name="cantidad"]').val());
        const precioCompra = parseFloat($('#form_save_to_product input[name="precio"]').val());

        if (!idalmacen || String(idalmacen).trim() === '') {
            toast_msg('Debe seleccionar un almacen.', 'warning');
            return;
        }

        if (!selectProduct || String(selectProduct).trim() === '') {
            toast_msg('Debe seleccionar un producto.', 'warning');
            return;
        }

        if (!isValidPositiveNumber(cantidad)) {
            toast_msg('Ingrese una cantidad valida.', 'warning');
            return;
        }

        if (isNaN(precioCompra) || Number(precioCompra) < 0) {
            toast_msg('Ingrese un precio de compra valido.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.add_product_buy') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                id: selectProduct,
                idalmacen: idalmacen,
                cantidad: cantidad,
                precio_compra: precioCompra
            },
            beforeSend: function() {
                toggleProductSaveButton(true);
            },
            success: function(r) {
                toggleProductSaveButton(false);
                toast_msg(r.msg, r.type || 'success');

                if (!r.status) {
                    return;
                }

                $('#modalAddToProduct').modal('hide');
                load_cart();
            },
            error: function(xhr) {
                toggleProductSaveButton(false);
                toast_msg(xhr.responseJSON?.msg || 'No se pudo agregar el producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: "json"
        });
    });

    $('body').on('click', '.btn-delete-product', function(event) {
        event.preventDefault();
        const cartKey = $(this).data('cart-key');

        $.ajax({
            url: "{{ route('admin.delete_product_buy') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                cart_key: cartKey
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                load_cart();
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo retirar el producto del detalle.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-down', function(event) {
        event.preventDefault();
        const cartKey = $(this).data('cart-key');
        const cantidad = parseInt($(this).data('cantidad'), 10);
        const cantidadEnviar = cantidad - 1;
        const precioCompra = parseFloat($(this).data('precio_compra'));

        if (cantidadEnviar <= 0) {
            toast_msg('La cantidad no puede ser menor a 1.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_buy') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                cart_key: cartKey,
                cantidad: cantidadEnviar,
                precio_compra: precioCompra
            },
            success: function(r) {
                toast_msg(r.msg, r.type || 'success');
                if (r.status) {
                    load_cart();
                }
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar la cantidad.', xhr.responseJSON?.type || 'error');
            },
            dataType: "json"
        });
    });

    $('body').on('click', '.btn-up', function(event) {
        event.preventDefault();
        const cartKey = $(this).data('cart-key');
        const cantidad = parseInt($(this).data('cantidad'), 10);
        const cantidadEnviar = cantidad + 1;
        const precioCompra = parseFloat($(this).data('precio_compra'));

        $.ajax({
            url: "{{ route('admin.store_product_buy') }}",
            method: "POST",
            data: {
                _token: "{{ csrf_token() }}",
                cart_key: cartKey,
                cantidad: cantidadEnviar,
                precio_compra: precioCompra
            },
            success: function(r) {
                toast_msg(r.msg, r.type || 'success');
                if (r.status) {
                    load_cart();
                }
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar la cantidad.', xhr.responseJSON?.type || 'error');
            },
            dataType: "json"
        });
    });

    $('body').on('change', '.input-precio-compra, .input-quantity', function() {
        const $input = $(this);
        const cantidad = $input.hasClass('input-quantity') ? $input.val() : $input.data('cantidad');
        const precioCompra = $input.hasClass('input-precio-compra') ? $input.val() : $input.data('precio_compra');
        const cartKey = $input.data('cart-key');

        if (!isValidPositiveNumber(cantidad)) {
            toast_msg('La cantidad debe ser mayor a cero.', 'warning');
            return;
        }

        if (precioCompra === '' || isNaN(precioCompra) || Number(precioCompra) < 0) {
            toast_msg('Solo se permiten numeros validos en el precio.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_buy') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                cart_key: cartKey,
                cantidad: cantidad,
                precio_compra: precioCompra
            },
            success: function(r) {
                toast_msg(r.msg, r.type || 'success');
                if (r.status) {
                    load_cart();
                }
            },
            error: function(xhr) {
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar el producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '#form_save_buy .btn-save', function(event) {
        event.preventDefault();

        const validation = existenRequeridosVacios();
        if (validation.status) {
            if (validation.ids.includes('serie') || validation.ids.includes('correlativo')) {
                toastr.warning('Complete los campos de serie y correlativo', 'Atencion');
                return;
            }

            if (validation.ids.includes('dni_ruc')) {
                toastr.warning('Seleccione el proveedor', 'Atencion');
                return;
            }

            if (validation.ids.includes('modo_pago')) {
                toastr.warning('Seleccione el modo de pago', 'Atencion');
                return;
            }
        }

        Swal.fire({
            title: '¿Esta seguro?',
            text: '¿Desea guardar la compra?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Si, guardar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: "{{ route('admin.save_buy') }}",
                method: "POST",
                data: $('#form_save_buy').serialize(),
                beforeSend: function() {
                    toggleBuySaveButton(true);
                },
                success: function(r) {
                    toggleBuySaveButton(false);

                    if (!r.status) {
                        toastr.error(r.msg, 'Error');
                        return;
                    }

                    window.location.href = "{{ route('admin.buys') }}";
                },
                error: function(xhr) {
                    toggleBuySaveButton(false);
                    toastr.error(xhr.responseJSON?.msg || 'Hubo un problema al guardar la compra.', 'Error');
                },
                dataType: "json"
            });
        });
    });

    $('#modalAddToProduct').on('shown.bs.modal', function() {
        $('#idalmacen').select2({
            placeholder: "[SELECCIONE]",
            dropdownParent: $('#modalAddToProduct'),
            width: '100%'
        });

        $('#product').select2({
            placeholder: "[BUSCAR PRODUCTO]",
            dropdownParent: $('#modalAddToProduct'),
            width: '100%'
        });
    });

    $('#modalAddToProduct').on('hidden.bs.modal', function() {
        resetProductModal();
    });

    $(document).ready(function() {
        initializeBuySelects();
        load_cart();
    });
</script>
