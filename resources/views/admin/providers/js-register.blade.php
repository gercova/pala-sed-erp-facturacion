<script>
    $(document).ready(function() {
        const modalProvider = new bootstrap.Modal(document.getElementById('modalProvider'));
        const modalElement = $('#modalProvider');
        let editingProviderId = null;

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

            if (code === '6') return 0;
            if (code === '1') return 1;
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

        function initProviderSelect2() {
            sortDocumentTypeOptions($('#provider_tipo_documento'));

            $('.select2-provider').each(function() {
                if ($(this).hasClass('select2-hidden-accessible')) {
                    $(this).select2('destroy');
                }
            });

            $('.select2-provider').select2({
                dropdownParent: modalElement,
                width: '100%',
                placeholder: 'Seleccionar...'
            });
        }

        function getProviderDocumentCode() {
            return String($('#provider_tipo_documento option:selected').data('code') || '').trim();
        }

        function toggleProviderSearchButton(isVisible, label = 'Consultar') {
            const btn = $('.btn-search-provider-doc');
            btn.toggleClass('d-none', !isVisible);
            btn.find('.provider-search-text').text(label);
        }

        function toggleProviderSearchLoading(isLoading) {
            $('.btn-search-provider-doc').prop('disabled', isLoading);
            $('.provider-search-text').toggleClass('d-none', isLoading);
            $('.provider-search-spinner').toggleClass('d-none', !isLoading);
        }

        function resetUbigeoSelects(options = {}) {
            const hideDepartment = options.hideDepartment === true;

            $('#provider_departamento').html('<option value=""></option>').trigger('change.select2');
            $('#provider_provincia').html('<option value=""></option>').trigger('change.select2');
            $('#provider_distrito').html('<option value=""></option>').trigger('change.select2');
            $('#provider_province_wrapper').addClass('d-none');
            $('#provider_district_wrapper').addClass('d-none');

            $('#provider_department_wrapper').toggleClass('d-none', hideDepartment);
        }

        function syncProviderDocumentSearchUI() {
            const documentCode = getProviderDocumentCode();
            const shouldSearch = documentCode === '1' || documentCode === '6';
            const shouldShowUbigeo = ['1', '4', '6'].includes(documentCode);

            toggleProviderSearchButton(shouldSearch, documentCode === '6' ? 'SUNAT' : 'RENIEC');

            if (shouldShowUbigeo) {
                $('#provider_department_wrapper').removeClass('d-none');
            } else {
                resetUbigeoSelects({ hideDepartment: false });
            }
        }

        function populateDepartments(response) {
            let html = '<option value=""></option>';
            $.each(response.departments || [], function(index, department) {
                html += `<option value="${department.codigo}">${department.descripcion}</option>`;
            });

            $('#provider_departamento').html(html).trigger('change.select2');
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

            $('#provider_departamento').val(departmentCode).trigger('change.select2');

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

                    $('#provider_province_wrapper').removeClass('d-none');
                    $('#provider_provincia').html(provinceHtml).trigger('change.select2');

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

                            $('#provider_district_wrapper').removeClass('d-none');
                            $('#provider_distrito').html(districtHtml).trigger('change.select2');
                        }
                    });
                }
            });
        }

        function resetProviderForm() {
            editingProviderId = null;
            modalElement.removeAttr('data-editing-id');
            $('#formProvider')[0].reset();
            $('#provider_id').val('');
            $('#provider_tipo_documento').val('').trigger('change');
            resetUbigeoSelects({ hideDepartment: false });
            toggleProviderSearchButton(false);
            $('#providerModalTitle').text('Registrar Proveedor');
            $('.btn-save-provider').text('Guardar');
        }

        $('body').on('click', '.btn-create-provider', function() {
            resetProviderForm();
            initProviderSelect2();
            loadUbigeo();
            modalProvider.show();
        });

        $('#provider_tipo_documento').on('change', function() {
            syncProviderDocumentSearchUI();
        });

        $('#provider_departamento').on('change', function() {
            const value = $(this).val();

            if (!value) {
                $('#provider_province_wrapper').addClass('d-none');
                $('#provider_district_wrapper').addClass('d-none');
                $('#provider_provincia').html('<option value=""></option>').trigger('change.select2');
                $('#provider_distrito').html('<option value=""></option>').trigger('change.select2');
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

                    $('#provider_province_wrapper').removeClass('d-none');
                    $('#provider_provincia').html(html).trigger('change.select2');
                    $('#provider_district_wrapper').addClass('d-none');
                    $('#provider_distrito').html('<option value=""></option>').trigger('change.select2');
                }
            });
        });

        $('#provider_provincia').on('change', function() {
            const value = $(this).val();
            const departamento = $('#provider_departamento').val();

            if (!value || !departamento) {
                $('#provider_district_wrapper').addClass('d-none');
                $('#provider_distrito').html('<option value=""></option>').trigger('change.select2');
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

                    $('#provider_district_wrapper').removeClass('d-none');
                    $('#provider_distrito').html(html).trigger('change.select2');
                }
            });
        });

        $('body').on('click', '.btn-search-provider-doc', function() {
            $.ajax({
                url: "{{ route('admin.search_provider_document_record') }}",
                method: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    type_document: $('#provider_tipo_documento').val(),
                    dni_ruc: $('#provider_dni_ruc').val().trim()
                },
                beforeSend: function() {
                    toggleProviderSearchLoading(true);
                },
                success: function(r) {
                    toggleProviderSearchLoading(false);

                    if (!r.status) {
                        toast_msg(r.msg, r.type || 'warning');
                        return;
                    }

                    $('#provider_razon_social').val(r.nombres);
                    $('#provider_direccion').val(r.direccion);

                    if (r.ubigeo) {
                        applyUbigeoCodes(
                            r.ubigeo.substring(0, 2),
                            r.ubigeo.substring(0, 4),
                            r.ubigeo.substring(0, 6)
                        );
                    }
                },
                error: function(xhr) {
                    toggleProviderSearchLoading(false);
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo consultar el documento.', xhr.responseJSON?.type || 'error');
                }
            });
        });

        $('body').on('click', '.btn-detail', function() {
            const id = $(this).data('id');

            $.ajax({
                url: "{{ route('providers.detail') }}",
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

                    editingProviderId = r.provider.id;
                    modalElement.attr('data-editing-id', r.provider.id);
                    $('#provider_id').val(r.provider.id);
                    $('#provider_tipo_documento').val(String(r.provider.iddoc)).trigger('change');
                    $('#provider_dni_ruc').val(r.provider.nro_documento);
                    $('#provider_razon_social').val(r.provider.nombres);
                    $('#provider_direccion').val(r.provider.direccion);
                    $('#provider_telefono').val(r.provider.telefono || '');
                    $('#provider_email').val(r.provider.email || '');

                    loadUbigeo().done(function() {
                        if (r.department_code) {
                            applyUbigeoCodes(r.department_code, r.province_code, r.district_code);
                        } else {
                            resetUbigeoSelects({ hideDepartment: false });
                        }
                    });

                    syncProviderDocumentSearchUI();
                    $('#providerModalTitle').text('Actualizar Proveedor');
                    $('.btn-save-provider').text('Actualizar');
                    initProviderSelect2();
                    modalProvider.show();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'No se pudo obtener el proveedor.', xhr.responseJSON?.type || 'error');
                }
            });
        });

        $('body').on('click', '.btn-save-provider', function() {
            const btn = this;
            const providerId = modalElement.attr('data-editing-id') || editingProviderId || $('#provider_id').val();
            const route = providerId ? "{{ route('providers.store') }}" : "{{ route('providers.save') }}";
            const customText = providerId ? 'Actualizando...' : 'Guardando...';
            const formData = $('#formProvider').serializeArray();

            if (providerId && !formData.some((field) => field.name === 'id')) {
                formData.push({ name: 'id', value: providerId });
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
                    if (typeof success_save_provider === 'function') {
                        success_save_provider(r.msg, r.type || 'success', r.last_id || providerId || null);
                    }
                    modalProvider.hide();
                    resetProviderForm();
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
                    url: "{{ route('providers.delete') }}",
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
                        toast_msg(xhr.responseJSON?.msg || 'No se pudo eliminar el proveedor.', xhr.responseJSON?.type || 'error');
                    }
                });
            });
        });

        $('#modalProvider').on('hidden.bs.modal', function() {
            resetProviderForm();
        });

        $('#modalProvider').on('shown.bs.modal', function() {
            initProviderSelect2();
        });

        initProviderSelect2();
        loadUbigeo();
        resetProviderForm();
    });
</script>
