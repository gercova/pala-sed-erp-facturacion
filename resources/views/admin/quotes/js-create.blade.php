<script>
    var setTimeOutBuscador = '';
    function open_modal_client()
    {}

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

    function success_save_client(msg = null, type = null, last_id = null) 
    {
        toast_msg(msg, type);
        load_clients();
        setTimeout(() => {
            $('#form_save_quote select[name="dni_ruc"]').val(last_id);
            $('#form_save_quote select[name="dni_ruc"]').trigger('change');
        }, 600);
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
                close_block('#layout-content');
                if (!r.status) {
                    toast_msg(r.msg, r.title, r.type);
                    return;
                }

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

    $('body').on('click', '.btn-add-product', function() {
        event.preventDefault();
        let idalmacen = $('input[name="idalmacenuser"]').val();
        $('#modalAddToProduct').modal('show');
        $('#form_save_to_product select[name="product"]').select2({
            dropdownParent: $('#modalAddToProduct .modal-body'),
            placeholder: "[SELECCIONE]",
            width: '100%'
        });

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
                }); 

                setTimeout(() => {
                    $('#form_save_to_product select[name="product"]').select2("open");
                    
                    // Usar el selector de jQuery para encontrar el input de select2 y hacer foco
                    setTimeout(() => {
                        $('.select2-container--open .select2-search__field').focus();
                    }, 100); // Esperar un momento para asegurarse de que el select2 esté completamente abierto
                }, 300);

            },
            dataType    : "json"
        });
        return;
    }

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

    function load_cart() {
        $.ajax({
            url: "{{ route('admin.load_cart_quotes') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}"
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.title, r.type);
                    return;
                }

                $('#tbody_quotes').html(r.html_cart);
                $('#wrapper_totals').html(r.html_totales);
            },
            dataType: 'json'
        });
    }

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
            url: "{{ route('admin.add_product_quote') }}",
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
                if (!r.status) {
                    $('#form_save_to_product .btn-save').prop('disabled', false);
                    $('#form_save_to_product .text-save').removeClass('d-none');
                    $('#form_save_to_product .text-saving').addClass('d-none');
                    toast_msg(r.msg, r.type);
                    return;
                }

                toast_msg(r.msg, r.type);
                $('#form_save_to_product .btn-save').prop('disabled', false);
                $('#form_save_to_product .text-save').removeClass('d-none');
                $('#form_save_to_product .text-saving').addClass('d-none');
                $('#form_save_to_product input[name="cantidad"]').val('1');
                $('#form_save_to_product input[name="precio"]').val('');
                $('#form_save_to_product select[name="product"]').val('').trigger('change');
                $('#form_save_to_product select[name="product"]').select2({
                    dropdownParent: $('#modalAddToProduct .modal-body'),
                    placeholder: "[SELECCIONE]",
                });
                $('#form_save_to_product select[name="product"]').select2("open");
                load_cart();
            },
            dataType: "json"
        });
    });

    $('body').on('click', '.btn-delete-product', function() {
        event.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('admin.delete_product_quote') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}",
                id: id
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.title, r.type);
                    return;
                }
                load_cart();
            },
            dataType: 'json'
        });
        return;
    });

    $('body').on('click', '.btn-down', function() {
        event.preventDefault();
        let id = $(this).data('id'),
            cantidad = parseInt($(this).data('cantidad')),
            cantidad_enviar = cantidad - 1,
            precio = parseFloat($(this).data('precio_venta'));

        if (cantidad_enviar <= 0) {
            toast_msg('Cantidad no puede ser menor a 1', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_quote') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad_enviar,
                precio: precio
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                toast_msg(r.msg, r.type);
                load_cart();
            },
            dataType: "json"
        });
    });

    $('body').on('click', '.btn-up', function() {
        event.preventDefault();
        let id = $(this).data('id'),
            cantidad = parseInt($(this).data('cantidad')),
            cantidad_enviar = cantidad + 1,
            precio = parseFloat($(this).data('precio_venta'));

        $.ajax({
            url: "{{ route('admin.store_product_quote') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad_enviar,
                precio: precio
            },
            success: function(r) {
                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                toast_msg(r.msg, r.type);
                load_cart();
            },
            dataType: "json"
        });
    });

    $('body').on('change', '.input-update', function() {
        let precio = $(this).val(),
            cantidad = $(this).data('cantidad'),
            id = $(this).data('id');

        if (precio.trim() == '') {
            return;
        }
        if (isNaN(precio)) {
            toast_msg('Solo se permiten números', 'warning');
            $(this).focus();
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_quote') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad,
                precio: precio
            },
            success: function(r) {
                if (!r.status) {
                    load_cart();
                    toast_msg(r.msg, r.type);
                    return;
                }

                toast_msg(r.msg, r.type);
                load_cart();
            },
            dataType: 'json'
        });
    });

    $('body').on('change', '.input-quantity', function() {
        let cantidad = $(this).val(),
            precio = $(this).data('precio_venta'),
            id = $(this).data('id');

        if (precio.trim() == '') {
            return;
        }
        if (isNaN(precio)) {
            toast_msg('Solo se permiten números', 'warning');
            $(this).focus();
            return;
        }

        $.ajax({
            url: "{{ route('admin.store_product_quote') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}",
                id: id,
                cantidad: cantidad,
                precio: precio
            },
            success: function(r) {
                if (!r.status) {
                    load_cart();
                    toast_msg(r.msg, r.type);
                    return;
                }

                toast_msg(r.msg, r.type);
                load_cart();
            },
            dataType: 'json'
        });
    });


    $('body').on('click', '#form_save_quote .btn-save', function(event) {
        event.preventDefault();
        Swal.fire({
            title: "¿Está seguro?",
            text: "¿Desea guardar la cotización?",
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, guardar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                let form = $('#form_save_quote').serialize();
                
                $.ajax({
                    url: "{{ route('admin.save_quote') }}",
                    method: "POST",
                    data: form,
                    beforeSend: function() {
                        $('#form_save_quote .btn-save').prop('disabled', true);
                        $('#form_save_quote .text-save').addClass('d-none');
                        $('#form_save_quote .text-saving').removeClass('d-none');
                    },
                    success: function(r) {
                        $('#form_save_quote .btn-save').prop('disabled', false);
                        $('#form_save_quote .text-save').removeClass('d-none');
                        $('#form_save_quote .text-saving').addClass('d-none');

                        if (!r.status) {
                            toastr.error(r.msg, "Error");
                            return;
                        }

                        window.location.href = "{{ route('admin.quotes') }}";
                    },
                    error: function() {
                        $('#form_save_quote .btn-save').prop('disabled', false);
                        $('#form_save_quote .text-save').removeClass('d-none');
                        $('#form_save_quote .text-saving').addClass('d-none');
                        toastr.error("Hubo un problema al guardar la cotización.", "Error");
                    },
                    dataType: "json"
                });
            }
        });
    });

    // Cargar clientes y carrito al iniciar
    load_clients();
    $(document).ready(function() {
        load_cart();
        $('#form_save_quote select[name="dni_ruc"]').select2();
        $('#form_save_quote select[name="modo_pago"]').select2({
            width: '100%'
        });
    });

</script>
