<script>
    $('body').on('click', '.btn-create-category', function() {
        event.preventDefault();
        $('#modalAddCategory').modal('show');
    });

    $('body').on('click', '.btn-detail', function() {
        event.preventDefault();
        let id = $(this).data('id');
        $.ajax({
            url: "{{ route('categories.detail') }}",
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
                $('#form_edit input[name="id"]').val(r.category.id);
                $('#form_edit input[name="descripcion"]').val(r.category.descripcion);
                $('#modalEditCategory').modal('show');
            },
            dataType: 'json'
        });
        return;
    });

    $('body').on('click', '.btn-store', function() {
        event.preventDefault();
        let form = $('#form_edit').serialize(),
            descripcion = $('#form_edit input[name="descripcion"]');

        if (descripcion.val() == '')
            descripcion.addClass('is-invalid');
        else
            descripcion.removeClass('is-invalid');

        if (descripcion.val().trim() != '') {
            $.ajax({
                url: "{{ route('categories.store') }}",
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

                    $('#modalEditCategory').modal('hide');
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
                url: "{{ route('categories.delete') }}",
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