<script>
    $('body').on('click', '.btn-create', function()
    {
        event.preventDefault();
        $('#modalAddCountry').modal('show');
    });

    $('body').on('click', '.btn-save', function()
    {
        event.preventDefault();
        let form            = $('#form_save').serialize(),
            descripcion     = $('#form_save input[name="descripcion"]'),
            prefijo         = $('#form_save input[name="prefijo"]'),
            codigo_telefono = $('#form_save input[name="codigo_telefono"]'),
            signo           = $('#form_save input[name="signo"]'),
            moneda          = $('#form_save input[name="moneda"]');

        if(descripcion.val() == '')
            descripcion.addClass('is-invalid');
        else
            descripcion.removeClass('is-invalid');

        if(prefijo.val() == '')
            prefijo.addClass('is-invalid');
        else
            prefijo.removeClass('is-invalid');

        if(codigo_telefono.val() == '')
            codigo_telefono.addClass('is-invalid');
        else
            codigo_telefono.removeClass('is-invalid');

        if(signo.val() == '')
            signo.addClass('is-invalid');
        else
            signo.removeClass('is-invalid');

        if(moneda.val() == '')
            moneda.addClass('is-invalid');
        else
            moneda.removeClass('is-invalid');

        if(descripcion.val().trim() != '' && prefijo.val().trim() != '' && codigo_telefono.val().trim() != '' && signo.val().trim() != '' && moneda.val().trim() != '')
        {
            $.ajax({
                url         :  "{{ route('countries.save') }}",
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

                    $('#modalAddCountry').modal('hide');
                    $('#form_save').trigger('reset');
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
            url         : "{{ route('countries.detail') }}",
            method      : 'POST',
            data        : {'_token' : "{{ csrf_token() }}",id: id},
            beforeSend  : function(){
                block_content('#layout-content');
            },
            success     : function(r){
                close_block('#layout-content');
                if(!r.status)
                {
                    toast_msg(r.msg, r.type);
                    return;
                }
                $('#form_edit input[name="id"]').val(r.country.id);
                $('#form_edit input[name="descripcion"]').val(r.country.descripcion);
                $('#form_edit input[name="prefijo"]').val(r.country.prefijo);
                $('#form_edit input[name="codigo_telefono"]').val(r.country.codigo_telefono);
                $('#form_edit input[name="signo"]').val(r.country.signo);
                $('#form_edit input[name="moneda"]').val(r.country.moneda);
                $('#modalEditCountry').modal('show');
            },
            dataType    : 'json'
        });
        return;
    });

    $('body').on('click', '.btn-store', function()
    {
        event.preventDefault();
        let form            = $('#form_edit').serialize(),
            descripcion     = $('#form_edit input[name="descripcion"]'),
            prefijo         = $('#form_edit input[name="prefijo"]'),
            codigo_telefono = $('#form_edit input[name="codigo_telefono"]'),
            signo           = $('#form_edit input[name="signo"]'),
            moneda          = $('#form_edit input[name="moneda"]');

        if(descripcion.val() == '')
            descripcion.addClass('is-invalid');
        else
            descripcion.removeClass('is-invalid');

        if(descripcion.val() == '')
            descripcion.addClass('is-invalid');
        else
            descripcion.removeClass('is-invalid');

        if(prefijo.val() == '')
            prefijo.addClass('is-invalid');
        else
            prefijo.removeClass('is-invalid');

        if(codigo_telefono.val() == '')
            codigo_telefono.addClass('is-invalid');
        else
            codigo_telefono.removeClass('is-invalid');

        if(signo.val() == '')
            signo.addClass('is-invalid');
        else
            signo.removeClass('is-invalid');

        if(moneda.val() == '')
            moneda.addClass('is-invalid');
        else
            moneda.removeClass('is-invalid');

        if(descripcion.val().trim() != '' && prefijo.val().trim() != '' && codigo_telefono.val().trim() != '' && signo.val().trim() != '' && moneda.val().trim() != '')
        {
            $.ajax({
                url         :  "{{ route('countries.store') }}",
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

                    $('#modalEditCountry').modal('hide');
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
            url: "{{ route('countries.delete') }}",
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