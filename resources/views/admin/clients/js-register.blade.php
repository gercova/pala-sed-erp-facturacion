<script>
    $(document).ready(function() {
        const modalClient = new bootstrap.Modal(document.getElementById('modalClient'));
        const modalElement = $('#modalClient');
        let editingClientId = null;

        function toggleBtnWaitMe(button, loading, loadingText = 'Procesando...') {
            const $button = $(button);
            const originalHtml = $button.data('original-html') || $button.html();

            if (! $button.data('original-html')) {
                $button.data('original-html', originalHtml);
            }

            if (loading) {
                $button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>' + loadingText);
                return;
            }

            $button.prop('disabled', false).html($button.data('original-html'));
        }

        function documentTypePriority(option) {
            const code = String($(option).data('code') || '').trim();

            if (code === '1') return 0;
            if (code === '6') return 1;
            if (code === '4') return 2;

            return 99;
        }

        function sortDocumentTypeOptions($select) {
            const options = $select.find('option').get();
            const placeholderOptions = options.filter((option) => option.value === '');
            const documentOptions = options
                .filter((option) => option.value !== '')
                .sort((left, right) => {
                    const leftLabel = $(left).text().trim();
                    const rightLabel = $(right).text().trim();
                    const priorityDiff = documentTypePriority(left) - documentTypePriority(right);

                    return priorityDiff !== 0 ? priorityDiff : leftLabel.localeCompare(rightLabel, 'es');
                });

            $select.empty().append(placeholderOptions).append(documentOptions);
        }

        function initClientSelect2() {
            sortDocumentTypeOptions($('#client_tipo_documento'));

            $('.select2-client').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });

            $('.select2-client').select2({
                dropdownParent: modalElement,
                width: '100%',
                placeholder: 'Seleccionar...'
            });
        }

        function getClientDocumentCode() {
            return String($('#client_tipo_documento option:selected').data('code') || '').trim();
        }

        function toggleClientSearchButton(isVisible, label = 'Consultar') {
            const btn = $('.btn-search-client-doc');
            btn.toggleClass('d-none', !isVisible);
            btn.find('.client-search-text').text(label);
        }

        function toggleClientSearchLoading(isLoading) {
            $('.btn-search-client-doc').prop('disabled', isLoading);
            $('.client-search-text').toggleClass('d-none', isLoading);
            $('.client-search-spinner').toggleClass('d-none', !isLoading);
        }

        function resetUbigeoSelects(options = {}) {
            const hideDepartment = options.hideDepartment === true;

            $('#client_departamento').html('<option value=""></option>').trigger('change.select2');
            $('#client_provincia').html('<option value=""></option>').trigger('change.select2');
            $('#client_distrito').html('<option value=""></option>').trigger('change.select2');
            $('#client_province_wrapper').addClass('d-none');
            $('#client_district_wrapper').addClass('d-none');

            $('#client_department_wrapper').toggleClass('d-none', hideDepartment);
        }

        function syncClientDocumentSearchUI() {
            const documentCode = getClientDocumentCode();
            const shouldSearch = documentCode === '1' || documentCode === '6';
            const shouldShowUbigeo = ['1', '4', '6'].includes(documentCode);

            toggleClientSearchButton(shouldSearch, documentCode === '6' ? 'SUNAT' : 'RENIEC');

            if (shouldShowUbigeo) {
                $('#client_department_wrapper').removeClass('d-none');
            } else {
                resetUbigeoSelects({ hideDepartment: false });
            }
        }

        function populateDepartments(response) {
            let html = '<option value=""></option>';
            $.each(response.departments || [], function(index, department) {
                html += `<option value="${department.codigo}">${department.descripcion}</option>`;
            });

            $('#client_departamento').html(html).trigger('change.select2');
        }

        function loadUbigeo() {
            return $.ajax({
                url: "{{ route('admin.load_ubigeo') }}",
                method: 'POST',
                data: { _token: "{{ csrf_token() }}" },
                success: function(response) {
                    populateDepartments(response);
                }
            });
        }

        function applyUbigeoCodes(departmentCode, provinceCode, districtCode) {
            if (!departmentCode) {
                return;
            }

            $('#client_departamento').val(departmentCode).trigger('change.select2');

            $.ajax({
                url: "{{ route('admin.load_provinces') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    codigo: departmentCode
                },
                success: function(response) {
                    let provinceHtml = '<option value=""></option>';
                    $.each(response.provinces || [], function(index, province) {
                        const selected = province.codigo === provinceCode ? 'selected' : '';
                        provinceHtml += `<option value="${province.codigo}" ${selected}>${province.descripcion}</option>`;
                    });

                    $('#client_province_wrapper').removeClass('d-none');
                    $('#client_provincia').html(provinceHtml).trigger('change.select2');

                    if (!provinceCode) {
                        return;
                    }

                    $.ajax({
                        url: "{{ route('admin.load_districts') }}",
                        method: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}",
                            codigo: provinceCode,
                            codigo_departamento: departmentCode
                        },
                        success: function(districtResponse) {
                            let districtHtml = '<option value=""></option>';
                            $.each(districtResponse.districts || [], function(index, district) {
                                const selected = district.codigo === districtCode ? 'selected' : '';
                                districtHtml += `<option value="${district.codigo}" ${selected}>${district.descripcion}</option>`;
                            });

                            $('#client_district_wrapper').removeClass('d-none');
                            $('#client_distrito').html(districtHtml).trigger('change.select2');
                        }
                    });
                }
            });
        }

        function resetClientForm() {
            editingClientId = null;
            modalElement.removeAttr('data-editing-id');
            $('#formClient')[0].reset();
            $('#client_id').val('');
            $('#client_tipo_documento').val('').trigger('change');
            resetUbigeoSelects({ hideDepartment: false });
            toggleClientSearchButton(false);
            $('#clientModalTitle').text('Registrar Cliente');
            $('.btn-save-client').text('Guardar');
        }

        $('body').on('click', '.btn-create-client, .btn-create-client-pos', function() {
            resetClientForm();
            initClientSelect2();
            loadUbigeo();
            modalClient.show();
        });

        $('#client_tipo_documento').on('change', function() {
            syncClientDocumentSearchUI();
        });

        $('#client_departamento').on('change', function() {
            const value = $(this).val();

            if (!value) {
                $('#client_province_wrapper').addClass('d-none');
                $('#client_district_wrapper').addClass('d-none');
                $('#client_provincia').html('<option value=""></option>').trigger('change.select2');
                $('#client_distrito').html('<option value=""></option>').trigger('change.select2');
                return;
            }

            $.ajax({
                url: "{{ route('admin.load_provinces') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    codigo: value
                },
                success: function(response) {
                    let html = '<option value=""></option>';
                    $.each(response.provinces || [], function(index, province) {
                        html += `<option value="${province.codigo}">${province.descripcion}</option>`;
                    });

                    $('#client_province_wrapper').removeClass('d-none');
                    $('#client_provincia').html(html).trigger('change.select2');
                    $('#client_district_wrapper').addClass('d-none');
                    $('#client_distrito').html('<option value=""></option>').trigger('change.select2');
                }
            });
        });

        $('#client_provincia').on('change', function() {
            const value = $(this).val();
            const departamento = $('#client_departamento').val();

            if (!value || !departamento) {
                $('#client_district_wrapper').addClass('d-none');
                $('#client_distrito').html('<option value=""></option>').trigger('change.select2');
                return;
            }

            $.ajax({
                url: "{{ route('admin.load_districts') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    codigo: value,
                    codigo_departamento: departamento
                },
                success: function(response) {
                    let html = '<option value=""></option>';
                    $.each(response.districts || [], function(index, district) {
                        html += `<option value="${district.codigo}">${district.descripcion}</option>`;
                    });

                    $('#client_district_wrapper').removeClass('d-none');
                    $('#client_distrito').html(html).trigger('change.select2');
                }
            });
        });

        $('body').on('click', '.btn-search-client-doc', function() {
            $.ajax({
                url: "{{ route('admin.search_client_document_record') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    type_document: $('#client_tipo_documento').val(),
                    dni_ruc: $('#client_dni_ruc').val().trim()
                },
                beforeSend: function() {
                    toggleClientSearchLoading(true);
                },
                success: function(r) {
                    toggleClientSearchLoading(false);

                    if (!r.status) {
                        toast_msg(r.msg, r.type || 'warning');
                        return;
                    }

                    $('#client_razon_social').val(r.nombres);
                    $('#client_direccion').val(r.direccion);

                    if (r.ubigeo) {
                        applyUbigeoCodes(
                            r.ubigeo.substring(0, 2),
                            r.ubigeo.substring(0, 4),
                            r.ubigeo.substring(0, 6)
                        );
                    }
                },
                error: function(xhr) {
                    toggleClientSearchLoading(false);
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo consultar el documento.', xhr.responseJSON?.type || 'error');
                }
            });
        });

        $('body').on('click', '.btn-detail', function() {
            const id = $(this).data('id');

            $.ajax({
                url: "{{ route('clients.detail') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function(r) {
                    if (!r.status) {
                        toast_msg(r.msg, r.type || 'warning');
                        return;
                    }

                    editingClientId = r.client.id;
                    modalElement.attr('data-editing-id', r.client.id);
                    $('#client_id').val(r.client.id);
                    $('#client_tipo_documento').val(String(r.client.iddoc)).trigger('change');
                    $('#client_dni_ruc').val(r.client.nro_documento);
                    $('#client_razon_social').val(r.client.nombres);
                    $('#client_direccion').val(r.client.direccion);
                    $('#client_telefono').val(r.client.telefono || '');
                    $('#client_email').val(r.client.email || '');

                    loadUbigeo().done(function() {
                        if (r.department_code) {
                            applyUbigeoCodes(r.department_code, r.province_code, r.district_code);
                        } else {
                            resetUbigeoSelects({ hideDepartment: false });
                        }
                    });

                    syncClientDocumentSearchUI();
                    $('#clientModalTitle').text('Actualizar Cliente');
                    $('.btn-save-client').text('Actualizar');
                    initClientSelect2();
                    modalClient.show();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo obtener el cliente.', xhr.responseJSON?.type || 'error');
                }
            });
        });

        $('body').on('click', '.btn-save-client', function() {
            const btn = this;
            const clientId = modalElement.attr('data-editing-id') || editingClientId || $('#client_id').val();
            const route = clientId ? "{{ route('clients.store') }}" : "{{ route('clients.save') }}";
            const customText = clientId ? 'Actualizando...' : 'Guardando...';
            const formData = $('#formClient').serializeArray();

            if (clientId && !formData.some((field) => field.name === 'id')) {
                formData.push({ name: 'id', value: clientId });
            }

            $.ajax({
                url: route,
                method: 'POST',
                data: $.param(formData),
                beforeSend: function() {
                    toggleBtnWaitMe(btn, true, customText);
                },
                success: function(r) {
                    toggleBtnWaitMe(btn, false);

                    if (!r.status) {
                        toast_msg(r.msg, r.type || 'warning');
                        return;
                    }

                    toast_msg(r.msg, r.type || 'success');
                    if (typeof success_save_client === 'function') {
                        success_save_client(r.msg, r.type || 'success', r.last_id || clientId || null);
                    }
                    modalClient.hide();
                    resetClientForm();
                    if ($('#table').length && $.fn.DataTable.isDataTable('#table')) {
                        $('#table').DataTable().ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    toggleBtnWaitMe(btn, false);
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo procesar la solicitud.', xhr.responseJSON?.type || 'error');
                }
            });
        });

        $('body').on('click', '.btn-confirm', function(event) {
            event.preventDefault();
            const id = $(this).data('id');

            Swal.fire({
                title: '¿Está seguro?',
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
                    url: "{{ route('clients.delete') }}",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id
                    },
                    success: function(r) {
                        toast_msg(r.msg, r.type || 'success');

                        if (r.status && $('#table').length && $.fn.DataTable.isDataTable('#table')) {
                            $('#table').DataTable().ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        toast_msg(xhr.responseJSON?.msg || 'No se pudo eliminar el cliente.', xhr.responseJSON?.type || 'error');
                    }
                });
            });
        });

        $('#modalClient').on('hidden.bs.modal', function() {
            resetClientForm();
        });

        $('#modalClient').on('shown.bs.modal', function() {
            initClientSelect2();
        });

        initClientSelect2();
        loadUbigeo();
        resetClientForm();
    });
</script>
