<script>
    $('body').on('click', '.btn-create', function()
    {
        event.preventDefault();
        $('#form_save select[name="tipo_documento"]').select2({
            placeholder: "[SELECCIONE]",
            dropdownParent: $('#modalAddSerie .modal-body'),
            width: '100%'
        });
        $('#form_save select[name="idcaja"]').select2({
            placeholder: "[SELECCIONE]",
            dropdownParent: $('#modalAddSerie .modal-body'),
            width: '100%'
        });
        $('#modalAddSerie').modal('show');
    });

    $('body').on('click', '.btn-save', function()
    {
        event.preventDefault();
        let form            = $('#form_save').serialize(),
            serie           = $('input[name="serie"]'),
            correlativo     = $('input[name="correlativo"]');

        if(serie.val() == '')
            serie.addClass('is-invalid');
        else
            serie.removeClass('is-invalid');

        if(correlativo.val() == '')
            correlativo.addClass('is-invalid');
        else
            correlativo.removeClass('is-invalid');

        if(serie.val().trim() != '' && correlativo.val().trim() != '')
        {
            $.ajax({
                url         :  "{{ route('series.save') }}",
                method      : 'POST',
                data        : form,
                beforeSend  : function(){
                    $('.btn-save').prop('disabled', true);
                    $('.text-saving').removeClass('d-none');
                    $('.text-save').addClass('d-none');
                },
                success     : function(r)
                {
                    $('.btn-save').prop('disabled', false);
                    $('.text-saving').addClass('d-none');
                    $('.text-save').removeClass('d-none');
                    toast_msg(r.msg, r.type);
                    if(!r.status)
                    {
                        return;
                    }

                    $('#modalAddSerie').modal('hide');
                    $('#form_save').trigger('reset');
                    $('#form_save select[name="tipo_documento"]').val(1).trigger('change');
                    $('#form_save select[name="idcaja"]').val(1).trigger('change');
                    reload_table();
                },
                dataType    : 'json'
            });
            return;
        }
    });

    $('body').on('click', '.btn-detail', function()
    {
        event.preventDefault();
        let id  = $(this).data('id');
        $.ajax({
            url         : "{{ route('series.detail') }}",
            method      : 'POST',
            beforeSend  : function(){
                block_content('#layout-content');
            },
            data        : {'_token' : "{{ csrf_token() }}",id: id},
            success     : function(r){
                close_block('#layout-content');
                if(!r.status)
                {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#form_edit input[name="id"]').val(r.serie.id);
                $('#form_edit input[name="serie"]').val(r.serie.serie);
                $('#form_edit input[name="correlativo"]').val(r.serie.correlativo);
                $('#form_edit select[name="tipo_documento"]').val(r.serie.idtipo_documento).select2({
                    placeholder: "[SELECCIONE]",
                    dropdownParent: $('#modalEditSerie .modal-body'),
                    width: '100%'
                });
                $('#form_edit select[name="idcaja"]').val(r.serie.idcaja).select2({
                    placeholder: "[SELECCIONE]",
                    dropdownParent: $('#modalEditSerie .modal-body'),
                    width: '100%'
                });
                $('#modalEditSerie').modal('show');
            },
            dataType    : 'json'
        });
        return;
    });

    $('body').on('click', '.btn-store', function()
    {
        event.preventDefault();
        let form            = $('#form_edit').serialize(),
            serie           = $('#form_edit input[name="serie"]'),
            correlativo     = $('#form_edit input[name="correlativo"]');

        if(serie.val() == '')
            serie.addClass('is-invalid');
        else
            serie.removeClass('is-invalid');

        if(correlativo.val() == '')
            correlativo.addClass('is-invalid');
        else
            correlativo.removeClass('is-invalid');

        if(serie.val().trim() != '' && correlativo.val().trim() != '')
        {
            $.ajax({
                url         :  "{{ route('series.store') }}",
                method      : 'POST',
                data        : form,
                beforeSend  : function(){
                    $('.btn-store').prop('disabled', true);
                    $('.text-store').addClass('d-none');
                    $('.text-storing').removeClass('d-none');
                },
                success     : function(r)
                {
                    $('.btn-store').prop('disabled', false);
                    $('.text-store').removeClass('d-none');
                    $('.text-storing').addClass('d-none');
                    toast_msg(r.msg, r.type);
                    if(!r.status)
                    {
                        return;
                    }

                    $('#modalEditSerie').modal('hide');
                    reload_table();
                },
                dataType    : 'json'
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
                eliminarRegistro(id);
            }
        }).catch(error => {
            console.error("Error en la confirmación:", error);
        });
    });

    function eliminarRegistro(id) {
        $.ajax({
            url: "{{ route('series.delete') }}",
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
</script>