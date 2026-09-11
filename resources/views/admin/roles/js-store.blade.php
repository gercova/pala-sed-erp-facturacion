<script>
    $('body').on('click', '.btn-create', function()
    {
        event.preventDefault();
        $('#modalAddRole').modal('show');
    });

    $('body').on('click', '.btn-save', function()
    {
        event.preventDefault();
        let form            = $('#form_save').serialize(),
            name            = $('#form_save input[name="name"]');

        if(name.val() == '')
            name.addClass('is-invalid');
        else
            name.removeClass('is-invalid');

        if(name.val().trim() != '')
        {
            $.ajax({
                url         :  "{{ route('roles.save') }}",
                method      : 'POST',
                data        : form,
                beforeSend  : function(){
                    $('.btn-save').prop('disabled', true);
                    $('.text-saving').removeClass('d-none');
                    $('.text-save').addClass('d-none');
                },
                success     : function(r)
                {
                    if(!r.status)
                    {
                        $('.btn-save').prop('disabled', false);
                        $('.text-saving').addClass('d-none');
                        $('.text-save').removeClass('d-none');
						toast_msg(r.msg, r.type);
                        return;
                    }

                    $('#modalAddRole').modal('hide');
                    $('#form_save').trigger('reset');
                    $('.btn-save').prop('disabled', false);
                    $('.text-save').removeClass('d-none');
                    $('.text-saving').addClass('d-none');
                    toast_msg(r.msg, r.type);
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
            url         : "{{ route('roles.detail') }}",
            method      : 'POST',
            data        : {'_token' : "{{ csrf_token() }}",id: id},
            beforeSend  : function(){
                block_content('#layout-content');
            },
            success     : function(r){
                if(!r.status)
                {
                    close_block('#layout-content');
                    toast_msg(r.msg, r.type);
                    return;
                }
                close_block('#layout-content');

                let html_permissions = '<div class="row g-3">';
                $('#modalEditRole input[name="id"]').val(r.data.role.id);
                $('#modalEditRole input[name="name"]').val(r.data.role.name);
                $.each(r.data.permissionGroups, function(index, group) {
                    html_permissions += `<div class="col-md-6">
                        <div class="card border h-100 shadow-sm">
                            <div class="card-body">
                                <h6 class="mb-1">${group.label}</h6>
                                <p class="text-muted small mb-0">${group.description ?? ''}</p>`;

                    $.each(group.permissions, function(permissionIndex, permission) {
                        html_permissions += `<div class="form-check mt-3">
                            <input class="form-check-input" type="checkbox" value="${permission.id}" name="permissions[]" id="permissions_${permission.id}" ${r.data.selPermissions.includes(permission.id) ? 'checked' : ''}>
                            <label class="form-check-label" for="permissions_${permission.id}">
                                ${permission.descripcion || permission.name}
                            </label>
                        </div>`;
                    });

                    html_permissions += `</div></div></div>`;
                });
                html_permissions += '</div>';
                $('#modalEditRole #wrapper_permissions').html(html_permissions);
                $('#modalEditRole').modal('show');
            },
            dataType    : 'json'
        });
        return;
    });

    $('body').on('click', '.btn-store', function()
    {
        event.preventDefault();
        let form        = $('#form_edit').serialize(),
            name        = $('#form_edit input[name="name"]');

        if(name.val() == '')
            name.addClass('is-invalid');
        else
            name.removeClass('is-invalid');

        if(name.val().trim() != '')
        {
            $.ajax({
                url         :  "{{ route('roles.store') }}",
                method      : 'POST',
                data        : form,
                beforeSend  : function(){
                    $('.btn-store').prop('disabled', true);
                    $('.text-store').addClass('d-none');
                    $('.text-storing').removeClass('d-none');
                },
                success     : function(r)
                {
                    if(!r.status)
                    {
                        $('.btn-store').prop('disabled', false);
                        $('.text-store').removeClass('d-none');
                        $('.text-storing').addClass('d-none');
                        toast_msg(r.msg, r.type);
                        return;
                    }

                    $('.btn-store').prop('disabled', false);
                    $('.text-store').removeClass('d-none');
                    $('.text-storing').addClass('d-none');
                    toast_msg(r.msg, r.type);
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
        title: "¿Desea eliminar el registro?",
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
                url: "{{ route('roles.delete') }}",
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
                        Swal.fire("Error", "Hubo un problema en la solicitud", "error");
                    }
                });
            }
        });
    });

</script>
