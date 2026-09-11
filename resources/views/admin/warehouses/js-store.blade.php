<script>
    $('body').on('click', '.btn-create', function() {
        event.preventDefault();
        $('#modalAddWarehouse').modal('show');
    });

    $('body').on('click', '.btn-save', function() {
        event.preventDefault();
        let form = $('#form_save').serialize(),
            descripcion = $('#form_save input[name="descripcion"]'),
            direccion   = $('#form_save input[name="direccion"]');

        if (descripcion.val() == '')
            descripcion.addClass('is-invalid');
        else
            descripcion.removeClass('is-invalid');

        if (direccion.val() == '')
            direccion.addClass('is-invalid');
        else
            direccion.removeClass('is-invalid');

        if (descripcion.val().trim() != '' && direccion.val().trim() != '') {
            $.ajax({
                url: "{{ route('warehouses.save') }}",
                method: 'POST',
                data: form,
                beforeSend: function() {
                    $('.btn-save').prop('disabled', true);
                    $('.text-saving').removeClass('d-none');
                    $('.text-save').addClass('d-none');
                },
                success: function(r) {
                    $('.btn-save').prop('disabled', false);
                    $('.text-saving').addClass('d-none');
                    $('.text-save').removeClass('d-none');
                    toast_msg(r.msg, r.type);
                    if (!r.status) {
                        return;
                    }

                    $('#modalAddWarehouse').modal('hide');
                    $('#form_save').trigger('reset');
                    reload_table();
                },
                dataType: 'json'
            });
            return;
        }
    });

    $('body').on('click', '.btn-detail', function() {
        event.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('warehouses.detail') }}",
            method: 'POST',
            data: {
                '_token': "{{ csrf_token() }}",
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
                $('#form_edit input[name="id"]').val(r.warehouse.id);
                $('#form_edit input[name="descripcion"]').val(r.warehouse.descripcion);
                $('#form_edit input[name="direccion"]').val(r.warehouse.direccion);
                $('#modalEditWarehouse').modal('show');
            },
            dataType: 'json'
        });
        return;
    });
    
    $('body').on('click', '.btn-store', function() {
        event.preventDefault();
        let form = $('#form_edit').serialize(),
            descripcion = $('#form_edit input[name="descripcion"]'),
            direccion   = $('#form_edit input[name="direccion"]');

        if (descripcion.val() == '')
            descripcion.addClass('is-invalid');
        else
            descripcion.removeClass('is-invalid');

        if (direccion.val() == '')
            direccion.addClass('is-invalid');
        else
            direccion.removeClass('is-invalid');

        if (descripcion.val().trim() != '' && direccion.val().trim() != '') {
            $.ajax({
                url: "{{ route('warehouses.store') }}",
                method: 'POST',
                data: form,
                beforeSend: function() {
                    $('.btn-store').prop('disabled', true);
                    $('.text-store').addClass('d-none');
                    $('.text-storing').removeClass('d-none');
                },
                success: function(r) {
                    $('.btn-store').prop('disabled', false);
                    $('.text-store').removeClass('d-none');
                    $('.text-storing').addClass('d-none');
                    toast_msg(r.msg, r.type);
                    if (!r.status) {
                        return;
                    }

                    $('#modalEditWarehouse').modal('hide');
                    reload_table();
                },
                dataType: 'json'
            });
            return;
        }
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
            $.ajax({
                url: "{{ route('warehouses.delete') }}",
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
                    if (!r.status) {
                        return;
                    }
                    reload_table();
                },
                error: function() {
                    toastr.error("Hubo un error en la solicitud", "Error");
                    }
                });
            }
        });
    });
</script>