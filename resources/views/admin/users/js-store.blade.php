<script>
    function initUserSelects(modalSelector) {
        $(`${modalSelector} select[name="idcaja"]`).select2({
            placeholder: '[SELECCIONE]',
            dropdownParent: $(`${modalSelector} .modal-body`),
            width: '100%'
        });

        $(`${modalSelector} select[name="role"]`).select2({
            placeholder: '[SELECCIONE]',
            dropdownParent: $(`${modalSelector} .modal-body`),
            width: '100%'
        });

        $(`${modalSelector} select[name="warehouse_ids[]"]`).select2({
            placeholder: '[SELECCIONE AL MENOS UN ALMACEN]',
            dropdownParent: $(`${modalSelector} .modal-body`),
            width: '100%'
        });
    }

    function serializeUserForm(formSelector) {
        const form = $(formSelector);
        return form.serialize();
    }

    function resetUserForm(formSelector) {
        const form = $(formSelector);
        form.trigger('reset');
        form.find('select[name="role"]').val('').trigger('change');
        form.find('select[name="warehouse_ids[]"]').val(null).trigger('change');
        form.find('select[name="estado"]').val('1').trigger('change');
        form.find('input[name="password"]').val('');
    }

    $('body').on('click', '.btn-create', function(event) {
        event.preventDefault();
        initUserSelects('#modalAddUser');
        resetUserForm('#form_save');
        $('#modalAddUser').modal('show');
    });

    $('body').on('click', '.btn-save', function(event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('users.save') }}",
            method: 'POST',
            data: serializeUserForm('#form_save'),
            beforeSend: function() {
                $('.btn-save').prop('disabled', true);
                $('.text-saving').removeClass('d-none');
                $('.text-save').addClass('d-none');
            },
            success: function(r) {
                $('.btn-save').prop('disabled', false);
                $('.text-saving').addClass('d-none');
                $('.text-save').removeClass('d-none');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#modalAddUser').modal('hide');
                resetUserForm('#form_save');
                toast_msg(r.msg, r.type);
                reload_table();
            },
            error: function(xhr) {
                $('.btn-save').prop('disabled', false);
                $('.text-saving').addClass('d-none');
                $('.text-save').removeClass('d-none');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo registrar el usuario.', xhr.responseJSON?.type || 'warning');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-detail', function(event) {
        event.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: "{{ route('users.detail') }}",
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

                initUserSelects('#modalEditUser');
                $('#form_edit input[name="id"]').val(r.user.id);
                $('#form_edit input[name="nombres"]').val(r.user.nombres);
                $('#form_edit input[name="user"]').val(r.user.user);
                $('#form_edit input[name="password"]').val('');
                $('#form_edit select[name="idcaja"]').val(String(r.user.idcaja)).trigger('change');
                $('#form_edit select[name="role"]').val(r.role || '').trigger('change');
                $('#form_edit select[name="estado"]').val(String(r.user.estado)).trigger('change');
                $('#form_edit select[name="warehouse_ids[]"]').val((r.warehouse_ids || []).map(String)).trigger('change');
                $('#modalEditUser').modal('show');
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo cargar el usuario.', xhr.responseJSON?.type || 'warning');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-store', function(event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('users.store') }}",
            method: 'POST',
            data: serializeUserForm('#form_edit'),
            beforeSend: function() {
                $('.btn-store').prop('disabled', true);
                $('.text-storing').removeClass('d-none');
                $('.text-store').addClass('d-none');
            },
            success: function(r) {
                $('.btn-store').prop('disabled', false);
                $('.text-storing').addClass('d-none');
                $('.text-store').removeClass('d-none');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#modalEditUser').modal('hide');
                toast_msg(r.msg, r.type);
                reload_table();
            },
            error: function(xhr) {
                $('.btn-store').prop('disabled', false);
                $('.text-storing').addClass('d-none');
                $('.text-store').removeClass('d-none');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar el usuario.', xhr.responseJSON?.type || 'warning');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-confirm', function(event) {
        event.preventDefault();
        let id = $(this).data('id');

        Swal.fire({
            title: '¿Desea eliminar el registro?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            $.ajax({
                url: "{{ route('users.delete') }}",
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
                error: function(xhr) {
                    Swal.close();
                    toast_msg(xhr.responseJSON?.msg || 'Hubo un problema en la solicitud.', xhr.responseJSON?.type || 'warning');
                }
            });
        });
    });

    $('body').on('click', '.btn-roles', function(event) {
        event.preventDefault();
        let id = $(this).data('id');

        $.ajax({
            url: "{{ route('users.view_role') }}",
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

                let htmlRoles = '';
                $('#modalUpdateRole input[name="id"]').val(r.data.user.id);
                $('#modalUpdateRole input[name="usuario"]').val(r.data.user.nombres);

                $.each(r.data.roles, function(index, role) {
                    htmlRoles += `
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="radio" value="${role.name}" name="roles[]" id="role_${role.id}" ${r.data.selRoles.includes(role.name) ? 'checked' : ''}>
                            <label class="form-check-label" for="role_${role.id}">
                                ${role.name}
                            </label>
                        </div>
                    `;
                });

                $('#wrapper_roles').html(htmlRoles);
                $('#modalUpdateRole').modal('show');
            },
            error: function(xhr) {
                close_block('#layout-content');
                toast_msg(xhr.responseJSON?.msg || 'No se pudieron cargar los roles.', xhr.responseJSON?.type || 'warning');
            },
            dataType: 'json'
        });
    });

    $('body').on('click', '.btn-update-role', function(event) {
        event.preventDefault();

        $.ajax({
            url: "{{ route('users.update_role') }}",
            method: 'POST',
            data: $('#form_update_role').serialize(),
            beforeSend: function() {
                $('#modalUpdateRole .btn-update-role').prop('disabled', true);
                $('#modalUpdateRole .text-update-role').addClass('d-none');
                $('#modalUpdateRole .text-saving-role').removeClass('d-none');
            },
            success: function(r) {
                $('#modalUpdateRole .btn-update-role').prop('disabled', false);
                $('#modalUpdateRole .text-update-role').removeClass('d-none');
                $('#modalUpdateRole .text-saving-role').addClass('d-none');

                if (!r.status) {
                    toast_msg(r.msg, r.type);
                    return;
                }

                $('#modalUpdateRole').modal('hide');
                toast_msg(r.msg, r.type);
                reload_table();
            },
            error: function(xhr) {
                $('#modalUpdateRole .btn-update-role').prop('disabled', false);
                $('#modalUpdateRole .text-update-role').removeClass('d-none');
                $('#modalUpdateRole .text-saving-role').addClass('d-none');
                toast_msg(xhr.responseJSON?.msg || 'No se pudo actualizar el rol.', xhr.responseJSON?.type || 'warning');
            },
            dataType: 'json'
        });
    });
</script>
