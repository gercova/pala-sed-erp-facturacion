<script>
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

    function toggleButtons(buttonSelector, textSelector, spinnerSelector, loading) {
        const $button = $(buttonSelector);
        const $text = $(textSelector);
        const $spinner = $(spinnerSelector);

        $button.prop('disabled', loading);
        $text.toggleClass('d-none', loading);
        $spinner.toggleClass('d-none', !loading);
    }

    function updateSunatEnvironmentState(value) {
        const isProduction = String(value) === '1';

        $('#sunat-environment-badge')
            .attr('class', 'badge ' + (isProduction ? 'bg-success-soft text-success' : 'bg-warning-soft text-warning') + ' fw-semibold')
            .text(isProduction ? 'Producción' : 'Beta');

        $('#sunat-environment-text').text(
            isProduction ? 'Listo para entorno productivo.' : 'Modo pruebas activo para integraciones y validaciones.'
        );
    }

    function load_ubigeo() {
        $.ajax({
            url: "{{ route('admin.load_ubigeo') }}",
            method: 'POST',
            data: { '_token': "{{ csrf_token() }}" },
            success: function(r) {
                let html_department = '<option value=""></option>';
                $.each(r.departments, function(index, department) {
                    let selected = (r.department && department.codigo === r.department.codigo) ? 'selected' : '';
                    html_department += `<option value="${department.codigo}" ${selected}>${department.descripcion}</option>`;
                });

                $('select[name="departamento"]').html(html_department).trigger('change.select2');

                if (r.ubigeo != null) {
                    $('#wrapper_province, #wrapper_district').removeClass('d-none');

                    let html_province = '<option value=""></option>';
                    $.each(r.provinces, function(i, p) {
                        let sel = (r.province && p.codigo === r.province.codigo) ? 'selected' : '';
                        html_province += `<option value="${p.codigo}" ${sel}>${p.descripcion}</option>`;
                    });
                    $('select[name="provincia"]').html(html_province).trigger('change.select2');

                    let html_district = '<option value=""></option>';
                    $.each(r.districts, function(i, d) {
                        let sel = (r.district && d.codigo === r.district.codigo) ? 'selected' : '';
                        html_district += `<option value="${d.codigo}" ${sel}>${d.descripcion}</option>`;
                    });
                    $('select[name="distrito"]').html(html_district).trigger('change.select2');
                }
            },
            dataType: 'json'
        });
    }

    $(document).ready(function() {
        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        $(".select2_department, .select2_province, .select2_district").select2({
            placeholder: "[SELECCIONE]",
            width: '100%'
        });

        load_ubigeo();

        $('select[name="departamento"]').on('change', function() {
            let value = $(this).val();
            if (!value) return;

            $.ajax({
                url: "{{ route('admin.load_provinces') }}",
                method: 'POST',
                data: {
                    '_token': "{{ csrf_token() }}",
                    codigo: value
                },
                success: function(r) {
                    let html_province = '<option value=""></option>';
                    $.each(r.provinces, function(index, province) {
                        html_province += `<option value="${province.codigo}">${province.descripcion}</option>`;
                    });

                    $('#wrapper_province').removeClass('d-none');
                    $('select[name="provincia"]').html(html_province).trigger('change.select2');
                    $('select[name="distrito"]').html('<option value=""></option>').trigger('change.select2');
                    $('#wrapper_district').addClass('d-none');
                },
                dataType: 'json'
            });
        });

        $('select[name="provincia"]').on('change', function() {
            let value = $(this).val();
            let codigo_departamento = $('select[name="departamento"]').val();
            if (!value) return;

            $.ajax({
                url: "{{ route('admin.load_districts') }}",
                method: 'POST',
                data: {
                    '_token': "{{ csrf_token() }}",
                    codigo: value,
                    codigo_departamento: codigo_departamento
                },
                success: function(r) {
                    let html_district = '<option value=""></option>';
                    $.each(r.districts, function(index, district) {
                        html_district += `<option value="${district.codigo}">${district.descripcion}</option>`;
                    });

                    $('#wrapper_district').removeClass('d-none');
                    $('select[name="distrito"]').html(html_district).trigger('change.select2');
                },
                dataType: 'json'
            });
        });

        $('#company_logo').on('change', function() {
            const file = this.files[0];
            const maxSize = 2 * 1024 * 1024;

            if (!file) {
                return;
            }

            if (!['image/jpeg', 'image/png'].includes(file.type)) {
                toast_msg('El logo debe estar en formato JPG, JPEG o PNG.', 'warning');
                this.value = '';
                return;
            }

            if (file.size > maxSize) {
                toast_msg('El logo no debe superar los 2MB.', 'warning');
                this.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#preview-logo, #header-company-logo').attr('src', e.target.result);
            };
            reader.readAsDataURL(file);
        });

        $('input[name="servidor_sunat"]').on('change', function() {
            updateSunatEnvironmentState($(this).val());
        });

        $('#certificado').on('change', function() {
            const file = this.files[0];

            if (!file) {
                $('#certificate-selected-name').text('');
                return;
            }

            if (!file.name.toLowerCase().endsWith('.pfx')) {
                toast_msg('El certificado debe estar en formato .pfx.', 'warning');
                this.value = '';
                $('#certificate-selected-name').text('');
                return;
            }

            $('#certificate-selected-name').text('Archivo seleccionado: ' + file.name);
        });

        $('#cobrar_igv').on('change', function() {
            const statusText = $('#status-igv');
            if (this.checked) {
                statusText.text('Régimen General (18%)').removeClass('text-success').addClass('text-primary');
            } else {
                statusText.text('Exonerado (Ley Amazonía)').removeClass('text-primary').addClass('text-success');
            }
        });

        $('body').on('click', '.btn-save-info', function(e) {
            e.preventDefault();
            let btn = $(this);
            let form = new FormData($('#form-info')[0]);

            $.ajax({
                url: "{{ route('business.save_info') }}",
                method: 'POST',
                data: form,
                cache: false,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    toggleBtnWaitMe(btn, true, 'Guardando...');
                },
                success: function(r) {
                    toggleBtnWaitMe(btn, false);
                    toast_msg(r.msg, r.type);

                    if (r.logo_url) {
                        $('#header-company-logo, #preview-logo').attr('src', r.logo_url);
                    }
                },
                error: function(xhr) {
                    toggleBtnWaitMe(btn, false);
                    toast_msg(xhr.responseJSON?.msg || 'Error al conectar con el servidor', xhr.responseJSON?.type || 'error');
                }
            });
        });

        $('body').on('click', '.btn-save-user', function(e) {
            e.preventDefault();
            let btn = $(this);
            let form = new FormData($('#form_info_user')[0]);

            $.ajax({
                url: "{{ route('business.save_sunat') }}",
                method: 'POST',
                data: form,
                cache: false,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    toggleBtnWaitMe(btn, true, 'Actualizando...');
                },
                success: function(r) {
                    toggleBtnWaitMe(btn, false);
                    toast_msg(r.msg, r.type);

                    if (r.certificate_name) {
                        $('#certificate-current-badge').text(r.certificate_name);
                        $('#certificate-status-badge')
                            .attr('class', 'badge bg-success-soft text-success fw-semibold')
                            .text('Cargado');
                        $('#certificate-status-text').text(r.certificate_name);
                        $('#certificate-selected-name').text('');
                        $('#certificado').val('');
                    }

                    updateSunatEnvironmentState($('input[name="servidor_sunat"]:checked').val());
                },
                error: function(xhr) {
                    toggleBtnWaitMe(btn, false);
                    toast_msg(xhr.responseJSON?.msg || 'Error al conectar con el servidor', xhr.responseJSON?.type || 'error');
                }
            });
        });
    });
</script>
