<script>
    function open_modal_client() {}
    function load_clients() {
        $.ajax({
            url: "{{ route('admin.load_clients') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}"
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                let html_clients = '';
                $.each(r.clients, function(index, client) {
                    html_clients +=
                        `<option value="${client.id}">${client.nro_documento + ' - ' + client.nombres}</option>`;
                });

                $('#form_save_quote select[name="dni_ruc"]').html(html_clients).select2({
                    width: '100%'
                });
            },
            dataType: 'json'
        });
        return;
    }

    function success_save_client(msg = null, type = null, last_id = null) {
        toast_msg(msg, type);
        load_clients();
        setTimeout(() => {
            $('#form_save_quote select[name="dni_ruc"]').val(last_id);
            $('#form_save_quote select[name="dni_ruc"]').trigger('change');
        }, 500);
    }

    $('#form_save_quote select[name="dni_ruc"]').val("{{ $quote->idcliente }}").select2({
        width: '100%'
    });
    $('#form_save_quote select[name="modo_pago"]').val("{{ $quote->idpago }}").select2({
        width: '100%'
    });

    $('body').on('click', '.btn-add-product', function() {
        event.preventDefault();
        $('#modalAddToProduct').modal('show');
        let idalmacen = $('input[name="idalmacenuser"]').val();
        $('#form_save_to_product select[name="product"]').select2({
            dropdownParent: $('#modalAddToProduct .modal-body'),
            placeholder: "[SELECCIONE]",
            width: '100%'
        });

        load_products_warehouse(idalmacen);
        $('#form_save_to_product select[name="idalmacen"]').val("").select2({
            placeholder: "[SELECCIONE]",
            dropdownParent: $('#modalAddToProduct .modal-body'),
            width: '100%'
        });

        $('#modalAddToProduct #wrapper-product').addClass('d-none');
    });

    function load_products_warehouse(idalmacen) {
        $.ajax({
            url         : "{{ route('admin.get_products_by_idwarehouse') }}",
            method      : "POST",
            data        : {
                '_token': "{{ csrf_token() }}",
                idalmacen: idalmacen
            },
            beforeSend  : function(){
                block_content('#layout-content');
            },
            success     : function(r){
                if (!r.status) {
                    close_block('#layout-content');
                    toast_msg(r.msg, r.title, r.type);
                    return;
                }

                close_block('#layout-content');
                $('#modalAddToProduct #wrapper-product').removeClass('d-none');
                let productos = r.productos,
                    html      = '<option value=""></option>';
                $.each(productos, function(index, producto) {
                    html += `<option value="${producto.idproducto}" data-idalmacen="${producto.idalmacen}">${producto.producto}</option>`;
                });

                $('#form_save_to_product select[name="product"]').html(html).select2({
                    placeholder: "[SELECCIONE]",
                    dropdownParent: $('#modalAddToProduct .modal-body'),
                    width: '100%'
                }); 

                $('#form_save_to_product select[name="idalmacen"]').val(idalmacen).select2({
                    placeholder: "[SELECCIONE]",
                    dropdownParent: $('#modalAddToProduct .modal-body'),
                    width: '100%'
                });

                setTimeout(() => {
                    $('#form_save_to_product select[name="product"]').select2("open");
                    setTimeout(() => {
                        $('.select2-container--open .select2-search__field').focus();
                    }, 100); 
                }, 300);

            },
            dataType    : "json"
        });
        return;
    }

    $('body').on('change', '#form_save_to_product select[name="idalmacen"]', function() {
        let value = $(this).val();
        if(value.trim() == "") {
            toast_msg("Seleccione un establecimiento", "warning");
            return;
        }

        $.ajax({
            url         : "{{ route('admin.get_products_by_idwarehouse') }}",
            method      : "POST",
            data        : {
                '_token': "{{ csrf_token() }}",
                idalmacen: value
            },
            beforeSend  : function(){
                block_content('#layout-content');
            },
            success     : function(r){
                if (!r.status) {
                    close_block('#layout-content');
                    toast_msg(r.msg, r.title, r.type);
                    return;
                }

                close_block('#layout-content');
                $('#modalAddToProduct #wrapper-product').removeClass('d-none');
                let productos = r.productos,
                    html      = '<option value=""></option>';
                $.each(productos, function(index, producto) {
                    html += `<option value="${producto.idproducto}" data-idalmacen="${producto.idalmacen}">${producto.producto}</option>`;
                });

                $('#form_save_to_product select[name="product"]').html(html).select2({
                    placeholder: "[SELECCIONE]",
                    dropdownParent: $('#modalAddToProduct .modal-body'),
                    width: '100%'
                }); 

                $('#form_save_to_product select[name="product"]').select2("open");
            },
            dataType    : "json"
        });
        return;
    });

    $('#modalAddToProduct select[name="product"]').on('change', function() {
        let value = $(this).val(),
            idalmacen = $('#modalAddToProduct select[name="product"]').find(":selected").data("idalmacen"),
            cantidad = $('#form_save_to_product input[name="cantidad"]').val();

        if (value.trim() == "") {
            return;
        }
        $.ajax({
            url: "{{ route('admin.get_product_buy_quote') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: value,
                idalmacen: idalmacen
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }
                $('#form_save_to_product input[name="precio"]').val(r.product.precio_venta);
            },
            dataType: "json"
        });
    });

    touch_down('#form_save_to_product input[name="cantidad"]', 'product');
    touch_up('#form_save_to_product input[name="cantidad"]', 'product');

    $('body').on('click', '#form_save_to_product .btn-save', function() {
        event.preventDefault();
        let select_product = $('#form_save_to_product select[name="product"]').val(),
            cantidad = parseFloat($('#form_save_to_product input[name="cantidad"]').val()),
            precio = parseFloat($('#form_save_to_product input[name="precio"]').val()),
            idalmacen = $('#form_save_to_product select[name="idalmacen"]').val();
        if (select_product.trim() == "") {
            toast_msg('Debe seleccionar un producto', 'warning');
            return;
        }
        if (cantidad <= 0) {
            toast_msg('Ingrese una cantidad válida', 'warning');
            return;
        }
        if (precio <= 0) {
            toast_msg('Ingrese un precio válido', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.get_product_quote_update') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: select_product,
                cantidad: cantidad,
                precio: precio,
                idalmacen: idalmacen
            },
            beforeSend: function() {
                $('#form_save_to_product .btn-save').prop('disabled', true);
                $('#form_save_to_product .text-save').addClass('d-none');
                $('#form_save_to_product .text-saving').removeClass('d-none');
            },
            success: function(r) {
                $('#form_save_to_product .btn-save').prop('disabled', false);
                $('#form_save_to_product .text-save').removeClass('d-none');
                $('#form_save_to_product .text-saving').addClass('d-none');
                toast_msg(r.msg, r.type);
                if (!r.status) {
                    return;
                }

                $('#form_save_to_product input[name="cantidad"]').val('1');
                $('#form_save_to_product input[name="precio"]').val('');
                $('#form_save_to_product select[name="product"]').val('').trigger('change');
                $('#form_save_to_product select[name="product"]').select2({
                    dropdownParent: $('#modalAddToProduct .modal-body'),
                    placeholder: "[SELECCIONE]",
                    width: '100%'
                });

                $('#form_save_to_product select[name="product"]').select2("open");
                sum_product(r.producto, r.cantidad);
            },
            dataType: "json"
        });
    });

    $('body').on('change', 'input[name="input-precio"]', function() {
        let precio = $(this).val(),
            cantidad = $(this).data('cantidad'),
            id = $(this).data('id'),
            idalmacen = $(this).data('idalmacen');

        if (precio.trim() == '') {
            return;
        }
        if (isNaN(precio)) {
            toast_msg('Solo se permiten números', 'warning');
            $(this).focus();
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_quote_update') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad,
                precio: precio,
                idalmacen: idalmacen
            },
            success: function(r) {
                toast_msg(r.msg, r.type);
                if (!r.status) {
                    return;
                }

                let producto = r.producto,
                    cantidad = r.cantidad,
                    precio = r.precio;
                sum_product_price(producto, cantidad, precio);
            },
            dataType: 'json'
        });
    });

    function sum_product_price(producto, cantidad_entra, precio) {
        $('#wrapper-tbody').each(function() {
            let html__new = '',
                id = $(this).find(`#tr__product__` + producto.id).find('input[name="idproducto"]'),
                cantidad = $(this).find(`#tr__product__` + producto.id).find('input[name="input-cantidad"]'),
                ultimo_tr = $(this).find('tr:last').find('td').eq(0).text(),
                idtable = $('input[name="idtable"]').val();

            if (id.val() == undefined) {
                html__new += `<tr id="tr__product__${producto.id}">
                                                <td class="d-none"><input type="hidden" name="idproducto" value="${producto.id}" data-idalmacen="${producto.idalmacen}"></td>
                                                <td>${producto.producto}</td>
                                                <td class="text-center">${producto.unidad}</td>
                                                <td class="text-right">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-subtract-line me-sm-1"></i></span>
                                                        <input type="text" data-id="${producto.id}" class="quantity-counter text-center form-control" value="1" name="input-cantidad">
                                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-add-line me-sm-1"></i></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><input type="text" class="form-control form-control-sm text-center" value="${parseFloat(producto.precio_venta).toFixed(2)}" data-cantidad="'1" data-id="${producto.id}" data-igv="${producto.igv}" data-impuesto="${producto.impuesto}" data-idalmacen="${producto.idalmacen}" name="input-precio"></td>

                                                <td class="text-center">${parseFloat(producto.precio_venta * 1).toFixed(2)}</td>
                                                <td class="text-center"><span data-id="${producto.id}" class="text-danger btn-delete-product" style="cursor: pointer;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x align-middle mr-25"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span></td>
                                </tr>`;

                $('#wrapper-tbody').append(html__new);
                calculate__totals();
            } else {
                $(this).find(`#tr__product__` + producto.id).find('td').eq(5).text(parseFloat(parseFloat(
                    precio) * parseInt(cantidad.val())).toFixed(2));
                $(this).find(`#tr__product__` + producto.id).find('input[name="input-precio"]').val(parseFloat(
                    precio).toFixed(2));
                calculate__totals();
            }
        });
    }

    $('body').on('click', '.btn-down', function() {
        event.preventDefault();
        let id = $(this).data('id'),
            cantidad = parseInt($(this).parent().find('input[name="input-cantidad"]').val()),
            cantidad_enviar = cantidad - 1,
            precio = parseFloat($(this).parent().parent().parent().find('td').eq(4).find(
                'input[name="input-precio"]').val()),
            idalmacen = $(this).data('idalmacen');

        if (cantidad_enviar < 1) {
            toast_msg('La cantidad no puede ser menor a 1', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_quote_update') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad_enviar,
                precio: precio,
                idalmacen: idalmacen
            },
            success: function(r) {
                toast_msg(r.msg, r.type);
                if (!r.status) {
                    return;
                }
                subtract_product_quantity(r.producto, r.cantidad, r.precio);
            },
            dataType: "json"
        });
    });

    function subtract_product_quantity(producto, cantidad_entra, precio) {
        $('#wrapper-tbody').each(function() {
            let html__new = '',
                id = $(this).find(`#tr__product__` + producto.id).find('input[name="idproducto"]'),
                cantidad = $(this).find(`#tr__product__` + producto.id).find('input[name="input-cantidad"]'),
                ultimo_tr = $(this).find('tr:last').find('td').eq(0).text();

            if (id.val() == undefined) {
                html__new += `<tr id="tr__product__${producto.id}">
                                                <td class="d-none"><input type="hidden" name="idproducto" value="${producto.id}" data-idalmacen="${producto.idalmacen}"></td>
                                                <td>${producto.producto}</td>
                                                <td class="text-center">${producto.unidad}</td>
                                                <td class="text-right">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-subtract-line me-sm-1"></i></span>
                                                        <input type="text" data-id="${producto.id}" class="quantity-counter text-center form-control" value="1" name="input-cantidad">
                                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-add-line me-sm-1"></i></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><input type="text" class="form-control form-control-sm text-center" value="${parseFloat(producto.precio_venta).toFixed(2)}" data-cantidad="'1" data-id="${producto.id}" data-igv="${producto.igv}" data-impuesto="${producto.impuesto}" data-idalmacen="${producto.idalmacen}" name="input-precio"></td>

                                                <td class="text-center">${parseFloat(producto.precio_venta * 1).toFixed(2)}</td>
                                                <td class="text-center"><span data-id="${producto.id}" class="text-danger btn-delete-product" style="cursor: pointer;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x align-middle mr-25"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span></td>
                                </tr>`;

                $('#wrapper-tbody').append(html__new);
                calculate__totals();
            } else {
                $(this).find(`#tr__product__` + producto.id).find('input[name="input-cantidad"]').val(parseInt(
                    cantidad_entra));
                $(this).find(`#tr__product__` + producto.id).find('td').eq(5).text(parseFloat(parseFloat(
                    precio) * parseInt(cantidad.val())).toFixed(2));
                calculate__totals();
            }
        });
    }

    $('body').on('click', '.btn-up', function() {
        event.preventDefault();
        let id = $(this).data('id'),
            cantidad = parseInt($(this).parent().find('input[name="input-cantidad"]').val()),
            cantidad_enviar = cantidad + 1,
            precio = parseFloat($(this).parent().parent().parent().find('td').eq(4).find(
                'input[name="input-precio"]').val()),
            idalmacen = $(this).data('idalmacen');

        $.ajax({
            url: "{{ route('admin.store_product_quote_update') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad_enviar,
                precio: precio,
                idalmacen: idalmacen
            },
            success: function(r) {
                toast_msg(r.msg, r.type);
                if (!r.status) {
                    return;
                }
                sum_product_quantity(r.producto, r.cantidad, r.precio);
            },
            dataType: "json"
        });
    });

    function sum_product_quantity(producto, cantidad_entra, precio) {
        $('#wrapper-tbody').each(function() {
            let html__new = '',
                id = $(this).find(`#tr__product__` + producto.id).find('input[name="idproducto"]'),
                cantidad = $(this).find(`#tr__product__` + producto.id).find('input[name="input-cantidad"]'),
                ultimo_tr = $(this).find('tr:last').find('td').eq(0).text();

            if (id.val() == undefined) {
                html__new += `<tr id="tr__product__${producto.id}">
                                                <td class="d-none"><input type="hidden" name="idproducto" data-idalmacen="${producto.idalmacen}" value="${producto.id}"></td>
                                                <td>${producto.producto}</td>
                                                <td class="text-center">${producto.unidad}</td>
                                                <td class="text-right">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-subtract-line me-sm-1"></i></span>
                                                        <input type="text" data-id="${producto.id}" class="quantity-counter text-center form-control" value="1" name="input-cantidad">
                                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-add-line me-sm-1"></i></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><input type="text" class="form-control form-control-sm text-center" value="${parseFloat(producto.precio_venta).toFixed(2)}" data-cantidad="'1" data-id="${producto.id}" data-igv="${producto.igv}" data-impuesto="${producto.impuesto}" data-idalmacen="${producto.idalmacen}" name="input-precio"></td>

                                                <td class="text-center">${parseFloat(producto.precio_venta * 1).toFixed(2)}</td>
                                                <td class="text-center"><span data-id="${producto.id}" class="text-danger btn-delete-product" style="cursor: pointer;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x align-middle mr-25"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span></td>
                                </tr>`;

                $('#wrapper-tbody').append(html__new);
                calculate__totals();
            } else {
                $(this).find(`#tr__product__` + producto.id).find('input[name="input-cantidad"]').val(
                    cantidad_entra);
                $(this).find(`#tr__product__` + producto.id).find('td').eq(5).text(parseFloat(parseFloat(
                    precio) * parseInt(cantidad.val())).toFixed(2));
                calculate__totals();
            }
        });
    }
    
    function sum_product(producto, cantidad_entra) {
        $('#wrapper-tbody').each(function() {
            let html__new = '',
                id = $(this).find(`#tr__product__` + producto.id).find('input[name="idproducto"]'),
                cantidad = $(this).find(`#tr__product__` + producto.id).find('input[name="input-cantidad"]'),
                precio_unitario = $(this).find(`#tr__product__` + producto.id).find(
                    'input[name="input-precio"]'),
                ultimo_tr = $(this).find('tr:last').find('td').eq(0).text();

            if (id.val() == undefined) {
                html__new += `<tr id="tr__product__${producto.id}">
                                                <td class="d-none"><input type="hidden" name="idproducto" value="${producto.id}" data-idalmacen="${producto.idalmacen}"></td>
                                                <td>${producto.producto}</td>
                                                <td class="text-center">${producto.unidad}</td>
                                                <td class="text-right">
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text btn-down" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-subtract-line me-sm-1"></i></span>
                                                        <input type="text" data-id="${producto.id}" class="quantity-counter text-center form-control" value="1" name="input-cantidad">
                                                        <span class="input-group-text btn-up" style="cursor: pointer;" data-id="${producto.id}" data-cantidad="1" data-precio="${parseFloat(producto.precio_venta).toFixed(2)}" data-idalmacen="${producto.idalmacen}"><i class="ri-add-line me-sm-1"></i></span>
                                                    </div>
                                                </td>
                                                <td class="text-center"><input type="text" class="form-control form-control-sm text-center" value="${parseFloat(producto.precio_venta).toFixed(2)}" data-cantidad="'1" data-id="${producto.id}" data-igv="${producto.igv}" data-impuesto="${producto.impuesto}"  data-idalmacen="${producto.idalmacen}" name="input-precio"></td>

                                                <td class="text-center">${parseFloat(producto.precio_venta * 1).toFixed(2)}</td>
                                                <td class="text-center"><span data-id="${producto.id}" class="text-danger btn-delete-product" style="cursor: pointer;"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x align-middle mr-25"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg></span></td>
                                </tr>`;

                $('#wrapper-tbody').append(html__new);
                calculate__totals();
            } else {
                $(this).find(`#tr__product__` + producto.id).find('input[name="input-cantidad"]').val(parseInt(
                    cantidad.val()) + cantidad_entra);
                $(this).find(`#tr__product__` + producto.id).find('td').eq(5).text(parseFloat(parseFloat(
                    precio_unitario.val()) * parseInt(cantidad.val())).toFixed(2));
                calculate__totals();
            }
        });
    }

    $('body').on('click', '.btn-delete-product', function() {
        event.preventDefault();
        $(this).closest('tr').remove();
        calculate__totals();
    });

    function calculate__totals() {
    let subtotal = 0,
        total = 0,
        igv = 0,
        exonerados = 0;

    $('#wrapper-tbody tr').each(function() {
            let precioVenta = parseFloat($(this).find('input[name="input-precio"]').val()) || 0;
            let cantidad = parseInt($(this).find('input[name="input-cantidad"]').val()) || 1;
            let igv__ = parseInt($(this).find('input[name="input-precio"]').data('igv')) || 0;
            let igv_c;
            switch (igv__) {
                case 0:
                    igv_c = 1;
                    break;
                case 10:
                    igv_c = 1.10;
                    break;
                case 18:
                    igv_c = 1.18;
                    break;
                default:
                    igv_c = 1;
            }

            let precioBase = precioVenta / igv_c;
            let igvProducto = (precioVenta - precioBase) * cantidad;
            
            igv += redondeado(igvProducto);
            exonerados += redondeado(precioBase * cantidad);
            subtotal += precioBase * cantidad;
        });

        total = subtotal + igv;

        $('.span__exonerada').text(exonerados.toFixed(2));
        $('.span__subtotal').text(subtotal.toFixed(2));
        $('.span__igv').text(igv.toFixed(2));
        $('.span__total').text(total.toFixed(2));
    }

    calculate__totals();

    function redondeado(numero, decimales = 2) {
        let factor = Math.pow(10, decimales);
        return (Math.round(numero * factor) / factor);
    }

    $('body').on('click', '.btn-save-quote', function(event) {
    event.preventDefault();
    
    Swal.fire({
        title: "¿Está seguro?",
        text: "¿Desea actualizar la cotización?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#28a745",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Sí, actualizar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            let productos = [],
                idtipo_comprobante = $('#form_save_quote select[name="idtipo_comprobante"]').val(),
                idcliente = $('#form_save_quote select[name="dni_ruc"]').val(),
                modo_pago = $('#form_save_quote select[name="modo_pago"]').val(),
                idquote = $('#form_save_quote input[name="idquote"]').val(),
                observaciones = $('#form_save_quote textarea[name="observaciones"]').val(),
                fecha_emision = $('#form_save_quote input[name="fecha_emision"]').val();

            $('#wrapper-tbody tr').each(function() {
                productos.push({
                    idproducto: $(this).find('input[name="idproducto"]').val(),
                    idalmacen: $(this).find('input[name="idproducto"]').data('idalmacen'),
                    cantidad: $(this).find('input[name="input-cantidad"]').val(),
                    precio: $(this).find('input[name="input-precio"]').val()
                });
            });

            let totales = {
                exonerada: $('.span__exonerada').text(),
                gravada: $('.span__gravada').text(),
                inafecta: $('.span__inafecta').text(),
                subtotal: $('.span__subtotal').text(),
                igv: $('.span__igv').text(),
                total: $('.span__total').text()
            };

            $.ajax({
                url: "{{ route('admin.gen_quote_update') }}",
                method: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'productos': JSON.stringify(productos),
                    'totales': JSON.stringify(totales),
                    'idtipo_comprobante': idtipo_comprobante,
                    'idcliente': idcliente,
                    'modo_pago': modo_pago,
                    'idquote': idquote,
                    'observaciones': observaciones,
                    'fecha_emision': fecha_emision
                },
                beforeSend: function() {
                    $('.btn-save-quote').prop('disabled', true);
                    $('.text-save-quote').addClass('d-none');
                    $('.text-saving-quote').removeClass('d-none');
                },
                success: function(r) {
                    $('.btn-save-quote').prop('disabled', false);
                    $('.text-save-quote').removeClass('d-none');
                    $('.text-saving-quote').addClass('d-none');

                    if (!r.status) {
                        toastr.error(r.msg, "Error");
                        return;
                    }

                    window.location.href = "{{ route('admin.quotes') }}";
                },
                error: function() {
                    $('.btn-save-quote').prop('disabled', false);
                    $('.text-save-quote').removeClass('d-none');
                    $('.text-saving-quote').addClass('d-none');
                    toastr.error("Hubo un problema al actualizar la cotización.", "Error");
                },
                dataType: "json"
            });
        }
    });
});

</script>
