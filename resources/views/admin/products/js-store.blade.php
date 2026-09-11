<script>
    function success_save_product(msg = null, type = null) {
        toast_msg(msg, type);
        reload_table();
    }

    function initEditProductSelects() {
        $('#modalEditProduct .edit-product-select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }

            $(this).select2({
                dropdownParent: $('#modalEditProduct'),
                width: '100%',
                placeholder: '[SELECCIONE]'
            });
        });
    }

    function syncEditProductType(type) {
        $('#edit_original_opcion').val(String(type || ''));
        $(`#form_edit_product input[name="opcion"][value="${type}"]`).prop('checked', true);
    }

    $('body').on('click', '.btn-view', function(e) {
        e.preventDefault();

        const id = $(this).data('id');

        $.ajax({
            url: "{{ route('products.view_detail') }}",
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

                $('.detail-code').html(r.data.codigo);
                $('.detail-barcode').html(r.data.codigo_barras);
                $('.detail-description').html(r.data.descripcion);
                $('.detail-category').html(r.data.categoria);
                $('.detail-buy').html(r.data.precio_compra);
                $('.detail-sale').html(r.data.precio_venta);

                $('#offCanvasDetail').offcanvas('show');
            },
            dataType: 'json'
        });
    });

    $('#modalEditProduct').on('shown.bs.modal', function() {
        initEditProductSelects();
    });

    $('body').on('click', '.btn-detail', function(e) {
        e.preventDefault();

        const id = $(this).data('id');

        $.ajax({
            url: "{{ route('products.detail') }}",
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
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                const form = $('#form_edit_product');
                form.find('input[name="id"]').val(r.product.id);
                form.find('input[name="codigo_interno"]').val(r.product.codigo_interno || '');
                form.find('input[name="codigo_barras"]').val(r.product.codigo_barras || '');
                form.find('input[name="codigo_sunat"]').val(r.product.codigo_sunat || '');
                form.find('input[name="descripcion"]').val(r.product.descripcion || '');
                syncEditProductType(r.product.opcion);

                $('#modalEditProduct').modal('show');

                setTimeout(function() {
                    initEditProductSelects();
                    form.find('select[name="idunidad"]').val(String(r.product.idunidad || '')).trigger('change');
                    form.find('select[name="idcategoria"]').val(String(r.product.idcategoria || '')).trigger('change');
                    form.find('select[name="idcodigo_igv"]').val(String(r.product.idcodigo_igv || $('#edit_idcodigo_igv').data('default') || '')).trigger('change');
                    syncEditProductType(r.product.opcion);
                }, 150);
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo obtener el producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    $('#form_edit_product input[name="opcion"]').on('change', function() {
        const originalType = String($('#edit_original_opcion').val() || '');
        const attemptedType = String($(this).val() || '');

        if (!originalType || originalType === attemptedType) {
            return;
        }

        syncEditProductType(originalType);
        toast_msg('No se puede cambiar el tipo de item al actualizar un producto o servicio.', 'warning');
    });

    $('body').on('click', '.btn-store-product', function(e) {
        e.preventDefault();

        const form = $('#form_edit_product');
        const descripcion = form.find('input[name="descripcion"]');
        const unidad = form.find('select[name="idunidad"]');
        const categoria = form.find('select[name="idcategoria"]');
        const afectacion = form.find('select[name="idcodigo_igv"]');

        descripcion.toggleClass('is-invalid', descripcion.val().trim() === '');
        unidad.toggleClass('is-invalid', !unidad.val());
        categoria.toggleClass('is-invalid', !categoria.val());
        afectacion.toggleClass('is-invalid', !afectacion.val());

        if (descripcion.val().trim() === '' || !unidad.val() || !categoria.val() || !afectacion.val()) {
            toast_msg('Completa los campos obligatorios para actualizar el producto.', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('products.store') }}",
            method: 'POST',
            data: form.serialize(),
            beforeSend: function() {
                $('.btn-store-product').prop('disabled', true);
                $('.text-store-product').addClass('d-none');
                $('.text-storing-product').removeClass('d-none');
            },
            success: function(r) {
                $('.btn-store-product').prop('disabled', false);
                $('.text-store-product').removeClass('d-none');
                $('.text-storing-product').addClass('d-none');

                if (!r.status) {
                    toast_msg(r.msg, r.type || 'warning');
                    return;
                }

                $('#modalEditProduct').modal('hide');
                toast_msg(r.msg, r.type);
                reload_table();
            },
            error: function(xhr) {
                $('.btn-store-product').prop('disabled', false);
                $('.text-store-product').removeClass('d-none');
                $('.text-storing-product').addClass('d-none');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar el producto.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-confirm', function(e) {
        e.preventDefault();

        const id = $(this).data('id');

        Swal.fire({
            title: '&iquest;Est&aacute; seguro?',
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
                url: "{{ route('products.delete') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
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

    $('body').on('click', '.btn-upload', function(e) {
        e.preventDefault();
        $('#modalUpload').modal('show');
    });

    $('body').on('click', '.btn-upload-product', function(e) {
        e.preventDefault();

        const form = new FormData($('#form_excel')[0]);

        $.ajax({
            url: "{{ route('products.upload_excel') }}",
            method: 'POST',
            data: form,
            cache: false,
            contentType: false,
            processData: false,
            beforeSend: function() {
                $('.btn-upload-product').prop('disabled', true);
                $('.text-upload-product').addClass('d-none');
                $('.text-uploads-product').removeClass('d-none');
            },
            success: function(r) {
                $('.btn-upload-product').prop('disabled', false);
                $('.text-upload-product').removeClass('d-none');
                $('.text-uploads-product').addClass('d-none');
                toast_msg(r.msg, r.type);

                if (!r.status) {
                    return;
                }

                $('#form_excel').trigger('reset');
                $('#modalUpload').modal('hide');
                reload_table();
            },
            error: function(xhr) {
                $('.btn-upload-product').prop('disabled', false);
                $('.text-upload-product').removeClass('d-none');
                $('.text-uploads-product').addClass('d-none');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo importar el catalogo.', xhr.responseJSON?.type || 'error');
            },
            dataType: 'json'
        });
    });
</script>
