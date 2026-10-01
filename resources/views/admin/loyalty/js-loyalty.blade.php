<script>
    let tableLoyalty = null;
    let tableLogs = null;

    function initLoyaltyTable() {
        tableLoyalty = $('#table-loyalty-clients').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('loyalty.get_clients') }}",
            columns: [
                { data: 'nro_documento', name: 'clients.nro_documento' },
                { data: 'nombres', name: 'clients.nombres', className: 'fw-semibold text-dark' },
                { data: 'telefono', name: 'clients.telefono' },
                { data: 'progreso', name: 'progreso', orderable: false, searchable: false },
                { data: 'estado_premio', name: 'estado_premio', orderable: false, searchable: false, className: 'text-center' },
                { data: 'premios_reclamados', name: 'client_loyalty.premios_reclamados', className: 'text-center' },
                { data: 'acciones', name: 'acciones', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[3, 'desc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    }

    function initLogsTable() {
        tableLogs = $('#table-loyalty-logs').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('loyalty.logs') }}",
            columns: [
                { data: 'created_at', name: 'created_at' },
                { data: 'usuario_nombre', name: 'usuario.nombres', defaultContent: 'Sistema' },
                { data: 'cambio_meta', name: 'meta_compras_nueva', orderable: false, searchable: false, className: 'text-center' },
                { data: 'cambio_bonif', name: 'bonificacion_nueva', orderable: false, searchable: false, className: 'text-center' },
                { data: 'estado_activo', name: 'activo_nuevo', orderable: false, searchable: false, className: 'text-center' },
                { data: 'motivo', name: 'motivo', defaultContent: '-' }
            ],
            order: [[0, 'desc']],
            language: {
                url: "//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            }
        });
    }

    $(document).ready(function() {
        initLoyaltyTable();
        initLogsTable();

        if (typeof feather !== 'undefined') {
            feather.replace();
        }

        // Preview dinámico del badge de regla al cambiar números
        $('#promo_meta_compras, #promo_bonificacion').on('input', function() {
            let meta = $('#promo_meta_compras').val() || 5;
            let bonif = $('#promo_bonificacion').val() || 1;
            $('#live-rule-badge').text(`${meta}+${bonif}`);
        });

        // Guardar configuración de la promoción inmediatamente
        $('#form-loyalty-settings').on('submit', function(e) {
            e.preventDefault();
            let $btn = $('#btn-save-settings');
            $btn.prop('disabled', true).html('<i class="ri-loader-4-line spin me-1"></i> Guardando...');

            $.ajax({
                url: "{{ route('loyalty.save_settings') }}",
                method: "POST",
                data: $(this).serialize(),
                success: function(r) {
                    toast_msg(r.msg, 'success');
                    if (r.rule_label) {
                        $('#header-rule-badge, #live-rule-badge, #summary-rule-label').text(r.rule_label);
                    }
                    if (r.promotion) {
                        let meta = r.promotion.meta_compras;
                        let bonif = r.promotion.bonificacion;
                        let bonusText = bonif > 1 ? `${bonif} GRATIS` : '1 GRATIS';
                        $('#summary-meta-text').text(`Por cada ${meta} compras, ¡${bonusText}!`);
                    }
                    tableLoyalty.ajax.reload();
                    tableLogs.ajax.reload();
                },
                error: function(xhr) {
                    toast_msg(xhr.responseJSON?.msg || 'Error al guardar configuración', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('<i class="ri-save-line me-1"></i> Guardar Cambios Inmediatamente');
                }
            });
        });

        // Canjear premio manualmente
        $(document).on('click', '.btn-redeem-reward', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            Swal.fire({
                title: '¿Canjear premio de fidelidad?',
                html: `Se descontará la meta de compras acumuladas de <strong>${name}</strong> manteniendo cualquier excedente para el siguiente ciclo.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0061f2',
                cancelButtonColor: '#6e7881',
                confirmButtonText: 'Sí, canjear premio',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('loyalty.redeem_reward') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", idcliente: id },
                        success: function(r) {
                            if (r.status) {
                                toast_msg(r.msg, 'success');
                                tableLoyalty.ajax.reload();
                            } else {
                                toast_msg(r.msg, 'warning');
                            }
                        },
                        error: function(xhr) {
                            toast_msg('Error al procesar canje', 'error');
                        }
                    });
                }
            });
        });

        // Sumar compra manual
        $(document).on('click', '.btn-add-loyalty-point', function() {
            let id = $(this).data('id');
            let name = $(this).data('name');

            Swal.fire({
                title: 'Sumar compras acumuladas',
                html: `Sumar compras acumuladas para <strong>${name}</strong>:`,
                input: 'number',
                inputValue: 1,
                inputAttributes: { min: 1, max: 20, step: 1 },
                showCancelButton: true,
                confirmButtonText: 'Sumar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed && result.value) {
                    $.ajax({
                        url: "{{ route('loyalty.add_point') }}",
                        method: "POST",
                        data: { _token: "{{ csrf_token() }}", idcliente: id, cantidad: result.value },
                        success: function(r) {
                            toast_msg(r.msg, 'success');
                            tableLoyalty.ajax.reload();
                        },
                        error: function(xhr) {
                            toast_msg('Error al sumar compras', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
