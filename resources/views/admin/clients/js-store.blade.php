<script>
     function success_save_client(msg = null, type = null, idtipocomprobante = null, last_id = null) {
        toast_msg(msg, type);
        reload_table();
    }

    function open_modal_client() {}

    $('body').on('click', '.btn-detail', function() {
        event.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('clients.detail') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}",
                id: id
            },
            beforeSend: function() {
                block_content('#layout-content');
            },
            success: function(r) {
                if (!r.status) {
                    close_block('#layout-content');
                    toast_msg(r.msg, r.title, r.type);
                    return;
                }
                close_block('#layout-content');

                // Asignar valores a los inputs sin manejar ubicación
                $('#form_edit_client input[name="id"]').val(r.client.id);
                $('#form_edit_client input[name="nro_documento"]').val(r.client.nro_documento);
                $('#form_edit_client input[name="nombres"]').val(r.client.nombres);
                $('#form_edit_client input[name="direccion"]').val(r.client.direccion);
                $('#form_edit_client input[name="telefono"]').val(r.client.telefono ?? '');
                $('#form_edit_client input[name="email"]').val(r.client.email ?? '');

                // Mostrar el modal
                $('#modalEditClient').modal('show');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-confirm', function(event) {
        event.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: "¿Está seguro?",
            text: "Esta acción no se puede deshacer",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Sí, eliminar",
            cancelButtonText: "Cancelar"
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarRegistro(id);
            }
        }).catch(error => {
            console.error("Error en la confirmación:", error);
        });
    });

    function eliminarRegistro(id) {
        $.ajax({
            url: "{{ route('clients.delete') }}",
            method: "POST",
            data: {
                '_token': "{{ csrf_token() }}",
                id: id
            },
            beforeSend: function() {
                Swal.fire({
                    title: "Eliminando...",
                    text: "Por favor, espere",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
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
                Swal.fire("Error", "Hubo un error en la solicitud", "error");
            }
        });
    }

    $('body').on('click', '#form_edit_client .btn-store-client', function(event) {
    event.preventDefault();

    let form = $('#form_edit_client').serialize(),
        nro_documento = $('#form_edit_client input[name="nro_documento"]'),
        nombres = $('#form_edit_client input[name="nombres"]'),
        direccion = $('#form_edit_client input[name="direccion"]');

        // Validaciones
        if (nro_documento.val().trim() === '')
            nro_documento.addClass('is-invalid');
        else
            nro_documento.removeClass('is-invalid');

        if (nombres.val().trim() === '')
            nombres.addClass('is-invalid');
        else
            nombres.removeClass('is-invalid');

        if (direccion.val().trim() === '')
            direccion.addClass('is-invalid');
        else
            direccion.removeClass('is-invalid');

        // Si hay errores, no continuar
        if (nro_documento.hasClass('is-invalid') || nombres.hasClass('is-invalid') || direccion.hasClass('is-invalid')) {
            toast_msg('Por favor, complete los campos obligatorios.', 'warning');
            return;
        }

        // Envío AJAX
        $.ajax({
            url: "{{ route('clients.store') }}",
            method: 'POST',
            data: form,
            beforeSend: function() {
                $('.btn-store-client').prop('disabled', true);
                $('.text-store-client').addClass('d-none');
                $('.text-storing-client').removeClass('d-none');
            },
            success: function(r) {
                $('.btn-store-client').prop('disabled', false);
                $('.text-store-client').removeClass('d-none');
                $('.text-storing-client').addClass('d-none');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#modalEditClient').modal('hide');
                toast_msg(r.msg, r.type);
                reload_table();
            },
            dataType: 'json'
        });
    });


</script>